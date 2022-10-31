<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionCollection;
use App\Http\Resources\TransactionResource;
use App\Models\Bank;
use App\Models\Product;
use App\Models\ProductFile;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $req)
    {

        $isSuccess = true;
        $msg = 'Transaksi berhasil';
        $data = null;
        $stsTripay = null;

        $client_name = $req['client_name'];
        $phone_number = $req['phone_number'];
        $email = $req['email'];
        $description = $req['description'];
        $bank = $req['bank'];
        $coupon = $req['coupon'];
        $total_price = 0;
        $redirect = $req->redirect;
        $order_items = array();

        $invoice_id = $this->getInvoice();
        $unique_number = $this->getUniqueNumber();

        $total_price = $this->getTotalPrice($req->products);
        $user = array(
            "id" => 0,
            'name' => $client_name,
            'email' => $email,
            'user_detail' => ["phone" => $phone_number ]
        );
        
        if ($bank != 77) {
            $user = Auth::user();
        }
        
        if($total_price <= 0){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'ID Product / Stock tidak valid',
                'data' => 'ERROR'
            ]);
        }
        
        DB::beginTransaction();

        try{
            $transaction = Transaction::create([
                'invoice_id' => $invoice_id,
                'user_id' => $user->id,
                'total_price' => $total_price,
                'unique_number' => $unique_number,
                'discount' => 0,
                'client_name' => $client_name,
                'phone_number' => $phone_number,
                'email' => $email,
                'coupon' => $coupon,
                'bank_id' => $bank,
                'description' => $description,
                'status' => 0
            ]);

            $products = $req->products;
            $description_all = "";
            
            $zipPath = "file/transaction/".$invoice_id."/".$invoice_id.".zip";
            $zip = new ZipArchive;
            $zip->open('file/'.$invoice_id.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

            foreach ($products as $product) {
                $product_id =  $product['product_id'];
                $productDB = Product::where("product_id",$product_id)->first();
                $price =  $productDB->price;
                $qty =  $product['qty'];
                $description_trx =  "Buying ".$productDB->product_name." ".$qty." Pcs";
                $description_all .= "- ".$description_trx."\n";

                if($productDB->stock < $qty){
                    return response()->json([
                        'msg' => "Stok untuk produk ".$product_id." - ".$productDB->product_name." tidak cukup",
                        'isSuccess' => false
                    ]);
                }
                
                TransactionDetail::create([
                    'product_id' => $productDB->product_id,
                    'invoice_id' => $invoice_id,
                    'price' => $price,
                    'qty' => $qty,
                    'description' => $description_trx
                ]);

                $productDB->stock = $productDB->stock - $qty;
                $productDB->save();
                $product_files = ProductFile::whereNull('invoice_id')->where("product_id",$productDB->product_id)->take($qty)->get();

                //Create ZIP file and Update and Move Product file to folder transaction
                
                foreach ($product_files as $product_file) {
                    $newPath = 'file/transaction/'.$invoice_id.'/'.$product_file->product_id.'-'.$product_file->id.'-'.$product_file->filename;
                    Storage::move($product_file->path,$newPath);
                    $zip->addFile($newPath,$product_file->filename);
                    
                    $product_file->status = 1;
                    $product_file->description = "SOLD";
                    $product_file->path = $newPath;
                    $product_file->invoice_id = $invoice_id;
                    $product_file->save();
                }

                //set order_items for tripay
                $item = array(
                    'sku' => $productDB->product_id,
                    'name' => $productDB->product_name, 
                    'price' => $productDB->price ,
                    'quantity' => $qty,
                    'subtotal' => ((int)$productDB->price * (int)$qty),
                    'product_url' => env('FE_APP_URL').'/product/'.$productDB->product_id,
                    'image_url' => null,
                );
                array_push($order_items, $item);
            }
            $zip->close();
            Storage::move('file/'.$invoice_id.'.zip',$zipPath);

            
            $data = $transaction;
            if($isSuccess){
                //CREATE TRIPAY TRANSACTION
                if($bank == 77){
                    $request = new Request([
                        'bank' => $bank,
                        'invoice' => $invoice_id,
                        'total_price' => $total_price,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->user_detail->phone,
                        'order_items' => $order_items,
                        'redirect' => $req->redirect,
                    ]);
                    $TripayController = new TripayController();
                    $stsTripay = $TripayController->createTrancaction($request);
                    $redirect = $stsTripay->data->checkout_url;
                }else{
                    $redirect = env('FE_APP_URL').'/invoice/detail/'.$invoice_id;
                }

// CREATE TELEGRAM NOTIFICATION
$text = 
"<b>=== ORDER JURAGAN AKUN ===</b>
Email : ".$user->email."
Nama : ".$user->name."
No Telepon : ".$user->user_detail->phone."
Total Price : ".$total_price."
Unique Number : ".$unique_number."
Bank : ".$bank."
Kupon : ".$coupon."
Request : ".$description."

<b> DETAIL ORDER </b>
".$description_all;

                $TelegramController = new TelegramController();
                $stsTele = $TelegramController->createNotif($text);

                DB::commit();
            }else{
                DB::rollback();
                return response()->json([
                    'msg' => "Transaksi gagal, hubungi tim support via WA : ".env('PHONE_NUMBER'),
                    'isSuccess' => false
                ]);
            }
        }catch(Exception $e){
            report($e);
            $msg = "Terjadi kesalahan teknis, hubungi admin ";
            $data = $e->getMessage();
            DB::rollback();
        }

        

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
            // 'stsTele' => $stsTele,
            'stsTripay' => $stsTripay,
            'redirect' => $redirect
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getInvoice()
    {
        //get Invoice ID
        $lastTrx = Transaction::whereDate('created_at', Carbon::now())->orderBy('invoice_id', 'DESC')->pluck('invoice_id')->first();
        $formatInv = "INV";
        $dateNow = date('Ymd');
        $invoice = $formatInv.$dateNow."0001";
        //INV+Ymd length = 11

        try{
            if(isset($lastTrx)){
                $lastInvoice = substr($lastTrx,11); //0000 without INV+Ymd
                $intInvoice = $lastInvoice+1; // 1
                $lenZero = 4 - strlen($intInvoice); // 3
                $invoice =  $formatInv.$dateNow.substr($lastInvoice,0,$lenZero).$intInvoice; //INV000000000001
            }
        }catch(Exception $e){
            
        }

        return $invoice;
    }
    
    public function getUniqueNumber()
    {
        $digits = 3;
        return rand(pow(10, $digits-1), pow(10, $digits)-1);
    }

    public function getTotalPrice($products)
    {
        $total_price = 0;
        foreach ($products as $product) {
            $product_id = $product['product_id'];
            $qty = (int)$product['qty'];
            $prod = Product::find($product_id);
            $total_price = $total_price + ((int)$prod->price * $qty) ;
        }

        return $total_price;
    }
    
    public function get()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = TransactionResource::collection(Transaction::orderBy('invoice_id','DESC')->get());

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }
    
    public function getByInvoice(Request $req)
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $trx = Transaction::find($req->invoice_id);
        $data = new TransactionResource($trx);

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function getByRange(Request $req)
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $trx = Transaction::whereBetween('created_at',[$req->startDate, $req->endDate.' 23:59:59'])->get();
        
        if($trx->count() < 1){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Transaksi tidak ditemukan!',
                'data' => 'TRX NOT FOUND'
            ]);
        }
        $data = TransactionResource::collection($trx);

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function getActive()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = new TransactionCollection(TransactionResource::collection(Transaction::where('status',0)->get()));

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function getDone()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = new TransactionCollection(TransactionResource::collection(Transaction::where('status',1)->get()));

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function getRefund()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = new TransactionCollection(TransactionResource::collection(Transaction::where('status',2)->get()));

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function getExpired()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = new TransactionCollection(TransactionResource::collection(Transaction::where('status',3)->get()));

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function getCancel()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = new TransactionCollection(TransactionResource::collection(Transaction::where('status',9)->get()));

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function setPending(Request $request)
    {
        $isSuccess = true;
        $msg = 'Transaksi berhasil dipending';
        $data = Transaction::find($request->invoice_id);
        if(! $data){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Transaksi tidak ditemukan!',
                'data' => 'ID '.$request->invoice_id.' NOT FOUND'
            ]);
        }
        $data->status = 0;
        $data->save();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function setConfirm(Request $request)
    {
        $isSuccess = true;
        $msg = 'Transaksi berhasil dikonfirmasi';
        $data = Transaction::find($request->invoice_id);
        if(! $data){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Transaksi tidak ditemukan!',
                'data' => 'ID '.$request->invoice_id.' NOT FOUND'
            ]);
        }
        
        DB::beginTransaction();
        $data->status = 1;
        $data->save();
        
        $this->doJournalTrx($request->invoice_id); //Add Journal Transaction
        DB::commit();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => new TransactionResource($data),
        ]);
    }

    public function setRefund(Request $request)
    {
        $isSuccess = true;
        $msg = 'Transaksi berhasil direfund';
        $data = Transaction::find($request->invoice_id);
        if(! $data){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Transaksi tidak ditemukan!',
                'data' => 'ID '.$request->invoice_id.' NOT FOUND'
            ]);
        }
        $data->status = 2;
        $data->save();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => new TransactionResource($data),
        ]);
    }

    public function setExpired(Request $request)
    {
        $isSuccess = true;
        $msg = 'Transaksi berhasil diubah menjadi kadaluarsa';
        $data = Transaction::find($request->invoice_id);
        if(! $data){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Transaksi tidak ditemukan!',
                'data' => 'ID '.$request->invoice_id.' NOT FOUND'
            ]);
        }
        $data->status = 3;
        $data->save();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => new TransactionResource($data),
        ]);
    }

    public function setCancel(Request $request)
    {
        $isSuccess = true;
        $msg = 'Transaksi berhasil dicancel';
        $data = Transaction::find($request->invoice_id);
        if(! $data){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Transaksi tidak ditemukan!',
                'data' => 'ID '.$request->invoice_id.' NOT FOUND'
            ]);
        }
        DB::beginTransaction();
        $data->status = 9;
        $data->save();

        $this->reversalJournalTrx($request->invoice_id); //Reversal Journal Transaction
        DB::commit();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => new TransactionResource($data),
        ]);
    }

    public function doJournalTrx($invoice_id){
        $transaction = Transaction::find($invoice_id);
        if($transaction){
            //Update Bank Balance
            try{
                $bank = Bank::find($transaction->bank_id);
                if($bank){
                    $bank->balance = $bank->balance + $transaction->total_price;
                    $bank->save();
                }
            }catch(Exception $e){
                report($e);
                $msg = "Terjadi kesalahan saat update Bank Balance";
                $transaction = $e->getMessage();
                DB::rollback();
            }
        }
    }

    public function reversalJournalTrx($invoice_id){
        $transaction = Transaction::find($invoice_id);
        if($transaction){
            //Update Bank Balance
            try{
                $bank = Bank::find($transaction->bank_id);
                if($bank){
                    $bank->balance = $bank->balance - $transaction->total_price;
                    $bank->save();
                }
            }catch(Exception $e){
                report($e);
                $msg = "Terjadi kesalahan saat update Bank Balance";
                $transaction = $e->getMessage();
                DB::rollback();
            }
        }
    }

}

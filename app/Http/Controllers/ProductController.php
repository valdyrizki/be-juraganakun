<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductFile;
use App\Models\ProductImage;
use App\Models\Stock;
use App\Models\Transaction;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use ApiResponseTrait;
    public function store(CreateProductRequest $request)
    {
        $data = null;
        DB::beginTransaction();

        $image = $request->image;
        try {
            $data = Product::create([
                'product_id' => $request->product_id,
                'product_name' => $request->product_name,
                'cogs' => (float) str_replace(['.', ','], '', $request->cogs),
                'price' => (float) str_replace(['.', ','], '', $request->price),
                'description' => $request->description,
                'distributor' => $request->distributor,
                'category_id' => $request->category_id,
                'status' => $request->status,
                'user_create' => Auth::id()
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), false, 400);
        }

        if ($image != null) {
            $img_name = $image->getClientOriginalName();
            $maxFileId = ProductImage::max('id');
            $img_id = $maxFileId + 1;

            try {
                if ($image) {

                    // Save to disk
                    Storage::putFile('file', $image);
                    // Rename the Name
                    $path = 'image/product/' . $data->product_id . '-' . $img_id . '-' . $image->getClientOriginalName();
                    $path = str_replace(' ', '_', $path);
                    $path = str_replace('+', '-', $path);
                    Storage::move('file/' . $image->hashName(), $path);

                    $image = ProductImage::create([
                        'product_id' => $data->product_id,
                        'img_name' => $img_name,
                        'path' => $path,
                        'description' => 'Image product',
                        'user_create' => 0
                    ]);
                }
            } catch (Exception $e) {
                report($e);
                $out = new \Symfony\Component\Console\Output\ConsoleOutput();
                $out->writeln($e);
                DB::rollBack();
                return $this->errorResponse("Gagal upload image!", false, 400);
            }
        }

        DB::commit();

        return $this->successResponse($data, "Create product success!");
    }

    public function update(Request $request)
    {
        $data = Product::find($request->product_id);
        if (!$data) {
            return $this->errorResponse("ID Produk " . $request->product_id . " tidak ditemukan!", false, 400);
        }

        DB::beginTransaction();

        $image = $request->image;
        if ($image != null) {
            $img_name = $image->getClientOriginalName();

            try {
                if ($image) {

                    $maxImgId = ProductFile::max('id');
                    $img_id = $maxImgId + 1;
                    //remove temp file
                    $temp = ProductImage::where('product_id', $data->product_id)->first();
                    Storage::move($temp->path, '/temp/' . $temp->path);
                    $temp->path = 'temp/' . $temp->path;
                    $temp->product_id = 0;
                    $temp->status = 0;
                    $temp->description = 'Deleted by ' . Auth::id();
                    $temp->save();

                    // Save to disk
                    Storage::putFile('file', $image);
                    // Rename the Name
                    $path = 'image/product/' . $data->product_id . '-' . $img_id . '-' . $image->getClientOriginalName();
                    $path = str_replace(' ', '_', $path);
                    $path = str_replace('+', '-', $path);
                    Storage::move('file/' . $image->hashName(), $path);

                    $temp = ProductImage::where('product_id', $data->product_id)->delete();

                    $image = ProductImage::create([
                        'product_id' => $data->product_id,
                        'img_name' => $img_name,
                        'path' => $path,
                        'description' => 'Image product',
                        'user_create' => 0
                    ]);
                }
            } catch (Exception $e) {
                report($e);
                $out = new \Symfony\Component\Console\Output\ConsoleOutput();
                $out->writeln($e);
                DB::rollBack();
                return $this->errorResponse("Gagal upload file!", false, 400);
            }
        }

        $data->product_name = $request->product_name == null ? $data->product_name : $request->product_name;
        $data->cogs = $request->cogs == null ? $data->cogs : (float) str_replace(['.', ','], '', $request->cogs);
        $data->price = $request->price == null ? $data->price : (float) str_replace(['.', ','], '', $request->price);
        $data->description = $request->description == null ? $data->description : $request->description;
        $data->distributor = $request->distributor == null ? $data->distributor : $request->distributor;
        $data->category_id = $request->category_id == null ? $data->category_id : $request->category_id;
        $data->status = $request->status == null ? $data->status : $request->status;
        $data->user_update = Auth::id();
        $data->save();
        DB::commit();

        return $this->successResponse($data, "Update product success!");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        DB::beginTransaction();

        //Validasi
        $data = Product::find($request->id);
        if (!$data) {
            return $this->errorResponse("ID Produk " . $request->id . " tidak ditemukan!", false, 400);
        }
        if ($data->stock > 0) {
            return $this->errorResponse("Masih terdapat stock produk " . $request->id . "!", false, 400);
        }

        $productImage = ProductImage::where("product_id", $request->id)->first();
        if (!$productImage) {
            return $this->errorResponse("ID Produk Image " . $request->id . " tidak ditemukan!", false, 400);
        }

        $data->delete();
        $productImage->delete();
        Storage::delete("/" . $productImage->path);
        DB::commit();

        return $this->successResponse($data, "Delete product success!");
    }

    public function getAll()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        // return Product::all();
        $data = new ProductCollection(ProductResource::collection(Product::all()));
        return $this->successResponse($data, "Get All Products Success!");
    }

    public function get()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = new ProductCollection(ProductResource::collection(Product::where('status', 1)->get()));

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function getByCode(Request $req)
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = new ProductResource(Product::find($req->id));
        if (!$data) {
            return $this->errorResponse("ID Produk " . $req->id . " tidak ditemukan!", false, 400);
        }

        return $this->successResponse($data, "Get Products " . $req->id . " Success!");
    }

    public function getByCategory(Request $req)
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = new ProductCollection(ProductResource::collection(Product::where('category_id', $req->category_id)->get()));
        if (!$data) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Produk tidak ditemukan!',
                'data' => 'ID ' . $req->category_id . ' NOT FOUND'
            ], 400);
        }

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function setActive(Request $request)
    {
        $isSuccess = true;
        $msg = 'Produk berhasil diaktifkan';
        $data = Product::find($request->product_id);
        if (!$data) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Produk tidak ditemukan!',
                'data' => 'ID ' . $request->product_id . ' NOT FOUND'
            ], 400);
        }
        $data->status = 1;
        $data->save();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function setDisable(Request $request)
    {
        $isSuccess = true;
        $msg = 'Produk berhasil dinonaktifkan';
        $data = Product::find($request->product_id);
        if (!$data) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Produk tidak ditemukan!',
                'data' => 'ID ' . $request->product_id . ' NOT FOUND'
            ], 400);
        }
        $data->status = 9;
        $data->save();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function storeStock(Request $request)
    {
        $isSuccess = true;
        $msg = 'Stock berhasil ditambahkan';
        $data = Product::find($request->product_id);

        if (!$data) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Produk tidak ditemukan!',
                'data' => $request->all()
            ], 400);
        }

        DB::beginTransaction();

        $maxFileId = ProductFile::max('id');
        $files = $request->file('files');
        $totalFile = count($files);
        if ($totalFile > 0) {
            if ($totalFile > 200) {
                return response()->json([
                    'isSuccess' => false,
                    'msg' => 'Max Upload 200 File!',
                    'data' => $request->all()
                ], 400);
            }
            $i = 1;
            foreach ($files as $file) {
                $filename = $file->getClientOriginalName();
                $fileId = $maxFileId + $i;

                try {
                    // Save to disk
                    Storage::putFile('file', $file);
                    // Rename the Name
                    $path = 'file/product/' . $data->product_id . '/' . $data->product_id . '-' . $fileId . '-' . $file->getClientOriginalName();
                    $path = str_replace(' ', '_', $path);
                    $path = str_replace('+', '-', $path);
                    Storage::move('file/' . $file->hashName(), $path);

                    $isFileNmExist = ProductFile::where('filename', $filename)->first();
                    if (!$isFileNmExist) {
                        $file = ProductFile::create([
                            'product_id' => $data->product_id,
                            'filename' => $filename,
                            'path' => $path,
                            'description' => 'Upload ' . $i . '/' . $totalFile . 'File',
                            'code' => Str::orderedUuid(),
                            'user_create' => Auth::id()
                        ]);
                    } else {
                        return response()->json([
                            'isSuccess' => false,
                            'msg' => 'Filename ' . $filename . ' Already exist',
                            'data' => $request->all()
                        ], 400);
                    }
                } catch (Exception $e) {
                    report($e);
                    $out = new \Symfony\Component\Console\Output\ConsoleOutput();
                    $out->writeln($e);
                    $msg .= " - gagal upload stock";
                    DB::rollBack();
                    return response()->json([
                        'isSuccess' => false,
                        'msg' => 'Terjadi kesalahan saat upload file!',
                        'data' => $request->all()
                    ], 400);
                }
                $i++;
            }
        } else {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'File Kosong!',
                'data' => $request->all()
            ], 400);
        }

        try {
            Stock::create([
                'product_id' => $data->product_id,
                'stock_add' => $totalFile,
                'description' => 'Stock Before = ' . $data->stock,
                'user_create' => Auth::id()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Gagal simpan history stock!',
                'data' => $request->all()
            ], 400);
        }



        $data->stock = $data->stock + $totalFile;
        $data->save();

        DB::commit();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function downloadByCode(Request $request)
    {
        $file = ProductFile::where('code', $request->code)->first();
        if (!$file) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'File tidak ditemukan!',
                'data' => 'FILE NOT FOUND'
            ], 400);
        }

        $header = [
            'Content-Type' => 'application/*',
        ];
        $response = response()->download($file->path, $file->filename, $header);
        if (ob_get_length()) ob_end_clean();
        return $response;
    }

    public function downloadByInvoice(Request $request)
    {
        $invoice_id = $request->invoice_id;
        $file = ProductFile::where('invoice_id', $invoice_id)->get();
        if (!$file) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'File tidak ditemukan!',
                'data' => 'FILE NOT FOUND'
            ], 400);
        }

        //Perbaiki untuk ADMIN & SUPERUSER tidak bisa download file 
        $transaction = false;
        if (Auth::user()->level === "99" || Auth::user()->level === "10") {
            $transaction = Transaction::where('invoice_id', $invoice_id)->first();
        } else {
            $transaction = Transaction::where('invoice_id', $invoice_id)->where('user_id', Auth::id())->first();
        }

        if (!$transaction) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Transaksi Tidak Ditemukan!',
                'data' => 'TRANSACTION NOT FOUND'
            ], 400);
        }

        if ($transaction->status != 1 && Auth::id() != 99) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Transaksi Tidak Ditemukan!',
                'data' => 'TRANSACTION NOT FOUND'
            ], 400);
        }

        $header = [
            'Content-Type' => 'application/*',
        ];

        $response = null;
        if ($file->count() > 1) { //Jika lebih dari 1 akun, download zip file by invoice
            $response = response()->download('file/transaction/' . $invoice_id . '/' . $invoice_id . '.zip', $invoice_id . 'zip', $header);
        } else { //jika 1 akun, download 1 file tsb
            $response = response()->download($file[0]->path, $file[0]->filename, $header);
        }
        if (ob_get_length()) ob_end_clean();
        return $response;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Tripay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TripayController extends Controller
{
    public function createTrancaction(Request $req)
    {
        // dd($req->order_items);
        if ($req->bank == 77) {
            $apiKey       = env("TRIPAY_API_KEY");
            $privateKey   = env("TRIPAY_PRIVATE_KEY");
            $merchantCode = env("TRIPAY_MERCHANT_CODE");
            $merchantRef  = $req->invoice;
            $amount       = $req->total_price;
            $signature = hash_hmac('sha256', $merchantCode . $merchantRef . $amount, $privateKey);
            $redirect = $req->redirect;

            $data = [
                'method'         => 'QRIS',
                'merchant_ref'   => $merchantRef,
                'amount'         => $amount,
                'customer_name'  => $req->name,
                'customer_email' => $req->email,
                'customer_phone' => $req->phone,
                'order_items'    => $req->order_items,
                'return_url'   => $redirect,
                'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
                'signature'    => $signature
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_FRESH_CONNECT  => true,
                CURLOPT_URL            => env('TRIPAY_CREATE_TRX_URL'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => false,
                CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
                CURLOPT_FAILONERROR    => false,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query($data),
                CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
            ]);

            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);
            $stsTripay = empty($error) ? $response : $error;
            // $result = preg_replace('/[^A-Za-z0-9\-]/', '', $stsTripay); // Removes special chars;

            //Save log to tripay table
            Tripay::create([
                'invoice_id' => $merchantRef,
                'result' => $stsTripay,
            ]);

            return json_decode($stsTripay);
        }
    }

    public function callback(Request $request)
    {
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $json = $request->getContent();
        $signature = hash_hmac('sha256', $json, env("TRIPAY_PRIVATE_KEY"));

        if ($signature !== (string) $callbackSignature) {
            return Response::json([
                'success' => false,
                'message' => 'Invalid signature',
                'signature' => $signature,
                'json' => $json
            ]);
        }

        if ('payment_status' !== (string) $request->server('HTTP_X_CALLBACK_EVENT')) {
            return Response::json([
                'success' => false,
                'message' => 'Unrecognized callback event, no action was taken',
            ]);
        }

        $data = json_decode($json);
        $uniqueRef = $data->merchant_ref;
        $status = strtoupper((string) $data->status);
        $transaction = Transaction::where('invoice_id', $uniqueRef)->first();
        $transactionController = new TransactionController;
        $req = new Request();
        $req->invoice_id = $transaction->invoice_id;

        if (!$transaction) {
            return 'No invoice found for this unique ref: ' . $uniqueRef;
        }

        switch ($status) {
            case 'UNPAID':
                $transaction->update(['status' => 0]);
                $transactionController->setPending($req);
                return response()->json(['success' => true]);

            case 'PAID':
                $transaction->update(['status' => 1]);
                $transactionController->setConfirm($req);
                return response()->json(['success' => true]);

            case 'EXPIRED':
                $transaction->update(['status' => 3]);
                $transactionController->setExpired($req);
                return response()->json(['success' => true]);

            case 'FAILED':
                $transaction->update(['status' => 9]);
                $transactionController->setCancel($req);
                return response()->json(['success' => true]);

            default:
                return response()->json(['error' => 'Unrecognized payment status']);
        }
    }
}

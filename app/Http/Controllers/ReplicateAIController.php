<?php

namespace App\Http\Controllers;

use App\Models\FileManager;
use App\Models\ReplicateAI;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReplicateAIController extends Controller
{
    public function generateImage(Request $req)
    {

        $isSuccess = true;
        $msg = 'Generate AI berhasil diproses';
        $data = null;

        //Generate Image
        $param = [
            'version' => '1bfb924045802467cf8869d96b231a12e6aa994abfe37e337c63a4e49a8c6c41',
            'input' => [
                'prompt' => $req->prompt,
            ],
            'webhook' => env('APP_URL') . '/api/ai/webhook',
        ];

        // //TEST API
        // $param = [
        //     'version' => '5c7d5dc6dd8bf75c1acaa8565735e7986bc5b66206b55cca93cb72c9bf15ccaa',
        //     'input' => [
        //         'text' => 'Alice',
        //     ],
        //     'webhook' => 'https://backupbe.juraganakun.com/api/ai/webhook'
        // ];

        $client = new Client();
        try {
            $resp = $client->post('https://api.replicate.com/v1/predictions', [
                'headers' => [
                    'Authorization' => 'Token r8_Q78Opetzc5Cf3t7RmVk17cyWojLhElU164LAn',
                    'Content-Type' => 'application/json',
                ],
                'json' => $param
            ]);
            $response = json_decode($resp->getBody(), true);
            // Log::info(1);
            // Log::info($resp->id);
            Log::info(2);
            Log::info($response);
            Log::info(3);
            Log::info($resp->getBody());
            Log::info(4);
            Log::info($response['id']);
            Log::info(5);

            try {
                $data = ReplicateAI::create([
                    'id' => $response['id'],
                    'version' => $response['version'] ? $response['version'] : null,
                    'input' => $response['input'] ? json_encode($response['input']) : null,
                    'error' => $response['error'] ? json_encode($response['error']) : null,
                    'status' => $response['status'] ? $response['status'] : null,
                    'created_at' => $response['created_at'] ? Carbon::parse($response['created_at'])->toDateTimeString() : null,
                    'url_get' => $response['urls']['get'] ? $response['urls']['get'] : null,
                    'url_cancel' => $response['urls']['cancel'] ? $response['urls']['cancel'] : null,
                ]);
            } catch (Exception $e) {
                Log::error("Error Ketika Save Replicate AI");
                Log::error($e);
                $isSuccess = false;
                $msg = 'Generate AI gagal diproses';
            }
        } catch (Exception $e) {
            Log::error("Error Ketika hit API Replicate AI");
            Log::error($e);
            $isSuccess = false;
            $msg = 'Generate AI gagal diproses';
        }

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ], $isSuccess ? 200 : 400);
    }

    public function enhanceImage(Request $req)
    {

        $isSuccess = true;
        $msg = 'Enhance AI berhasil diproses';
        $data = null;
        $path = null;
        $filename = null;
        Log::info($req->all());
        DB::beginTransaction();

        $image = $req->img;
        if ($image != null) {

            //Save input from FrontEnd to DB
            $img_name = $image->getClientOriginalName();
            $maxFileId = FileManager::max('id');
            $img_id = $maxFileId + 1;

            try {
                if ($image) {
                    // Save to disk
                    Storage::putFile('file', $image);
                    // Rename the Name
                    $filename = $img_id . '-' . $image->getClientOriginalName();
                    $filename = str_replace(' ', '_', $filename);
                    $filename = str_replace('+', '-', $filename);

                    $path = 'file/replicate/' . $filename;
                    Storage::move('file/' . $image->hashName(), $path);

                    $fileManager = FileManager::create([
                        'filename' => $filename,
                        'real_filename' => $img_name,
                        'path' => $path,
                        'description' => 'Enhance Image Input',
                        'status' => 1,
                        'code' => Str::orderedUuid(),
                    ]);
                }
            } catch (Exception $e) {
                Log::error($e);
                $msg .= " - Gagal simpan image input ke storage";
                DB::rollBack();

                return response()->json([
                    'isSuccess' => false,
                    'msg' => $msg,
                    'data' => $req->all(),
                ], $isSuccess ? 200 : 400);
            }

            //Hit API Replicate (Enhance Image)
            $param = [
                'version' => '9283608cc6b7be6b65a8e44983db012355fde4132009bf99d976b2f0896856a3',
                'input' => [
                    'img' => env('APP_URL') . '/file/replicate/' . $filename,
                ],
                'webhook' => env('APP_URL') . '/api/ai/webhook',
            ];

            $client = new Client();
            try {
                Log::info(0);
                $resp = $client->post('https://api.replicate.com/v1/predictions', [
                    'headers' => [
                        'Authorization' => 'Token ' . env('REPLICATE_TOKEN'),
                        "Content-Type" => "application/json",
                    ],
                    'json' => $param
                ]);

                Log::info(1);
                Log::info($resp->getBody());
                $response = json_decode($resp->getBody(), true);
                Log::info(2);
                Log::info($response);
                Log::info(3);

                try {
                    $data = ReplicateAI::create([
                        'id' => $response['id'],
                        'version' => $response['version'] ? $response['version'] : null,
                        'input' => $response['input'] ? json_encode($response['input']) : null,
                        'error' => $response['error'] ? json_encode($response['error']) : null,
                        'status' => $response['status'] ? $response['status'] : null,
                        'created_at' => $response['created_at'] ? Carbon::parse($response['created_at'])->toDateTimeString() : null,
                        'url_get' => $response['urls']['get'] ? $response['urls']['get'] : null,
                        'url_cancel' => $response['urls']['cancel'] ? $response['urls']['cancel'] : null,
                    ]);
                } catch (Exception $e) {
                    Log::error("Error Ketika Save Replicate AI");
                    Log::error($e);
                    $isSuccess = false;
                    $msg = 'Enhance AI gagal diproses';
                }
            } catch (Exception $e) {
                Log::error("Error Ketika hit API Replicate AI");
                Log::error($e);
                $isSuccess = false;
                $msg = 'Enhance AI gagal diproses';
            }
        }

        DB::commit();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ], $isSuccess ? 200 : 400);
    }

    public function getReplicateById(Request $req)
    {

        $isSuccess = true;
        $msg = 'Get Replicate berhasil!';
        $data = null;

        try {
            $data = ReplicateAI::find($req->id);
            if (!$data) {
                Log::error("Replicate Not Found!");
                $msg = 'ID Replicate tidak ditemukan!';
                $isSuccess = false;
            }
        } catch (Exception $e) {
            Log::error("Replicate Error!");
            Log::error($e);
            $msg = 'Get Replicate Error!';
            $isSuccess = false;
        }

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ], $isSuccess ? 200 : 400);
    }

    public function webHook(Request $request)
    {

        Log::info($request->all());
        $isSuccess = true;

        try {
            $replicateAI = ReplicateAI::find($request['id']);
            if ($replicateAI) {
                $replicateAI->output = $request['output'] != null ? json_encode($request['output']) : $replicateAI->output;
                $replicateAI->error = $request['error'] != null ? json_encode($request['error']) : $replicateAI->error;
                $replicateAI->status = $request['status'] != null ? $request['status'] : $replicateAI->status;
                $replicateAI->completed_at = $request['completed_at'] != null ? Carbon::parse(Carbon::parse('2023-10-05T04:42:48.945799Z')->toDateTimeString())->toDateTimeString() : $replicateAI->completed_at;
                $replicateAI->save();
            } else {
                Log::error("ID Replicate AI Tidak Ditemukan!");
                return response()->json([
                    'isSuccess' => false,
                    'msg' => "ID Replicate AI Tidak Ditemukan!",
                    'data' => $request->all(),
                ], 400);
            }
        } catch (Exception $e) {
            Log::error("Error Ketika Update Replicate AI");
            Log::error($e);
            $isSuccess = false;
        }
        return response()->json($isSuccess);
    }
}

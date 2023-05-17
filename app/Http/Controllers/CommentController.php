<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentCollection;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\CommentFile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommentController extends Controller
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
    public function store(Request $request)
    {
        $isSuccess = true;
        $msg = 'Comment berhasil ditambahkan';
        $data = null;

        DB::beginTransaction();
        $invoice_id = $request->invoice_id;

        $comment = Comment::create([
            'invoice_id' => $invoice_id,
            'comment' => $request->comment,
            'user_create' => Auth::id()
        ]);

        if ($request->hasFile('files')) {
            $maxFileId = CommentFile::max('id');
            $files = $request->file('files');
            $totalFile = count($files);
            if ($totalFile > 0) {
                $i = 1;
                foreach ($files as $file) {
                    $filename = $file->getClientOriginalName();
                    $fileId = $maxFileId + $i;

                    try {
                        // Save to disk
                        Storage::putFile('file', $file);
                        // Rename the Name
                        $path = 'file/transaction/' . $invoice_id . '/' . $fileId . '-' . $file->getClientOriginalName();
                        $path = str_replace(' ', '_', $path);
                        $path = str_replace('+', '-', $path);
                        Storage::move('file/' . $file->hashName(), $path);

                        $file = CommentFile::create([
                            'comment_id' => $comment->id,
                            'filename' => $filename,
                            'path' => $path,
                            'description' => 'Upload ' . $i . '/' . $totalFile . 'File',
                            'code' => Str::orderedUuid(),
                            'user_create' => Auth::id()
                        ]);
                    } catch (Exception $e) {
                        report($e);
                        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
                        $out->writeln($e);
                        $msg .= " - gagal upload image";
                        DB::rollBack();
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
        }

        DB::commit();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
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



    public function getByInvoice(Request $req)
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = new CommentCollection(CommentResource::collection(Comment::where('invoice_id', $req->invoice_id)->where('status', 1)->get()));
        if (!$data) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Comment tidak ditemukan!',
                'data' => 'ID ' . $req->product_id . ' NOT FOUND'
            ], 400);
        }

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function getFileByInvoice(Request $req)
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = CommentFile::where($req->invoice_id)->get();
        if (!$data) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Comment tidak ditemukan!',
                'data' => 'ID ' . $req->product_id . ' NOT FOUND'
            ], 400);
        }

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function downloadByCode(Request $request)
    {
        $file = CommentFile::where('code', $request->code)->first();
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

    public function delete(Request $request)
    {
        $isSuccess = true;
        $msg = 'Produk berhasil dihapus';
        $data = Comment::find($request->id);
        if (!$data) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Komentar tidak ditemukan!',
                'data' => 'COMMENT NOT FOUND'
            ], 400);
        }
        $data->status = 0;
        $data->save();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }
}

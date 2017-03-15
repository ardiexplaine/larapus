<?php

namespace App\Http\Controllers;

use illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use App\BorrowLog;
use App\Exceptions\BookException;
use App\Book;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use Yajra\Datatables\Html\Builder;
use Yajra\Datatables\Datatables;
use Excel;
use PDF;
use App\Author;
use Validator;

class BooksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Builder $htmlBuilder)
    {
        if ($request->ajax()) {
            $books = Book::with('author');

            return Datatables::of($books)
                ->addColumn('action', function($book) {
                    return view('datatable._action', [
                        'model'             =>  $book,
                        'form_url'          =>  route('books.destroy', $book->id),
                        'edit_url'          =>  route('books.edit', $book->id),
                        'confirm_message'   =>  'Yakin mau menghapus ' . $book->title . '?'
                    ]);
            })->make(true);
        }

        $html = $htmlBuilder
            ->addColumn(['data' => 'title', 'name' => 'title', 'title' => 'Judul'])
            ->addColumn(['data' => 'amount', 'name' => 'amount', 'title' => 'Jumlah'])
            ->addColumn(['data' => 'author.name', 'name' => 'author.name', 'title' => 'Penulis'])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => '', 'orderable' => false, 'searchable' => false]);

        return view('books.index')->with(compact('html'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('books.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBookRequest $request)
    {

        $book = Book::create($request->except('cover'));

        // Isi field cover jika ada cover yang diupload
        if ($request->hasFile('cover')) {
            // Mengambil file yang diupload
            $uploaded_cover = $request->file('cover');

            // Mengambil extension file
            $extension = $uploaded_cover->getClientOriginalExtension();

            // Membuat nama file random berikut extension
            $filename = md5(time()) . '.' . $extension;

            // Menyimpan cover ke folder public/img
            $destinationPath = public_path() . DIRECTORY_SEPARATOR . 'img';
            $uploaded_cover->move($destinationPath, $filename);

            // Mengisi field cover di book dengan filename yang harus dibuat
            $book->cover = $filename;
            $book->save();
        }

        Session::flash("flash_notification", [
            "level"     => "success",
            "message"   => "Berhasil menyimpan $book->title"
        ]);

        return redirect()->route('books.index');
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
        $book = Book::find($id);

        return view('books.edit')->with(compact('book'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBookRequest $request, $id)
    {

        $book = Book::find($id);
        if (!$book->update($request->all())) return redirect()->back();

        // Isi field cover jika ada cover yang diupload
        if ($request->hasFile('cover')) {
            // Mengambil file yang diupload berikut eksistensinya
            $filename = null;
            $uploaded_cover = $request->file('cover');

            // Mengambil extension file
            $extension = $uploaded_cover->getClientOriginalExtension();

            // Membuat nama file random berikut extension
            $filename = md5(time()) . '.' . $extension;

            // Menyimpan cover ke folder public/img
            $destinationPath = public_path() . DIRECTORY_SEPARATOR . 'img';
            $uploaded_cover->move($destinationPath, $filename);

            // Hapus cover lama, jika ada
            if ($book->cover) {
                $old_cover = $book->cover;
                $filepath = public_path() . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $book->cover;

                try {
                    File::delete($filepath);
                } catch (FileNotFoundException $e) {
                    // File sudah dihapus / tidak ada
                }
            }

            // Ganti field cover dengan cover yang baru
            $book->cover = $filename;
            $book->save();
        }

        Session::flash("flash_notification", [
            "level" => "success",
            "message" => "Berhasil menyimpan $book->title"
        ]);

        return redirect()->route('books.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $book = Book::find($id);
        $cover = $book->cover;

        if (!$book->delete()) return redirect()->back();

        // Handle hapus buku via Ajax
        if ($request->ajax()) return response()->json(['id' => $id]);
        
        // Hapus cover lama jika ada
        if ($cover) {
            $old_cover = $book->cover;
            $filepath = public_path() . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $book->cover;

            try {
                File::delete($filepath);
            } catch (FileNotFoundException $e) {
                // File sudah dihapus / tidak ada
            }
        }

        Session::flash("flash_notification", [
            "level"     =>  "success",
            "message"   =>  "Buku berhasil dihapus"
        ]);

        return redirect()->route('books.index');
    }

    public function borrow($id)
    {
        try {
            $book = Book::findOrFail($id);
            Auth::user()->borrow($book);

            Session::flash("flash_notification", [
                "level"      => "success",
                "message"    => "Berhasil meminjam $book->title"
            ]);

        } catch (BookException $e) {
            Session::flash("flash_notification", [
                "level"      => "danger",
                "message"    => $e->getMessage()
            ]);
        } catch (ModelNotFoundException $e) {
            Session::flash("flash_notification", [
                "level"      => "danger",
                "message"    => "Buku tidak ditemukan."
            ]);
        }

        return redirect('/');
    }

    public function returnBack($book_id)
    {
        $borrowLog = BorrowLog::where('user_id', Auth::user()->id)
            ->where('book_id', $book_id)
            ->where('is_returned', 0)
            ->first();

        if ($borrowLog) {
            $borrowLog->is_returned = true;
            $borrowLog->save();
        }

        Session::flash("flash_notification", [
            "level"      =>  "success",
            "message"    =>  "Berhasil mengembalikan " . $borrowLog->book->title 
        ]);

        return redirect('/home');
    }

    public function export() {
        return view('books.export');
    }


    public function exportPost(Request $request) {
        // Validasi
        $this->validate($request, [
            'author_id' => 'required',
            'type' => 'required|in:pdf,xls'
        ], [
            'author_id.required' => 'Anda belum memilih penulis. Pilih minimal 1 penulis.'
        ]);

        $books = Book::whereIn('id', $request->get('author_id'))->get();

        $handler = 'export' . ucfirst($request->get('type'));

        return $this->$handler($books);
    } 

    private function exportXls($books)
    {
        Excel::create('Data Buku Larapus', function($excel) use ($books) {
            // Set Property
            $excel->setTitle('Data Buku Larapus')->setCreator('Ryan Rahman Setiawan');

            $excel->sheet('Data Buku', function($sheet) use ($books) {
                $row = 1;
                $sheet->row($row, [
                    'Judul', 
                    'Jumlah',
                    'Stok',
                    'Penulis'
                ]);
                foreach ($books as $book) {
                    $sheet->row(++$row, [
                        $book->title,
                        $book->amount,
                        $book->stock,
                        $book->author->name
                    ]);
                }
            });
        })->export('xls');
    }

    public function exportPdf($books) {
        $pdf = PDF::loadview('pdf.books', compact('books'));

        return $pdf->download('books.pdf');
    }

    public function generateExcelTemplate() {
        Excel::create('Template Import Buku', function($excel) {
            // Set the properties
            $excel->setTitle('Template Import Buku')
                ->setCreator('Larapus')
                ->setCompany('Larapus')
                ->setDescription('Template import buku untuk Larapus');

            $excel->sheet('Data Buku', function($sheet) {
                $row = 1;
                $sheet->row($row, [
                    'judul',
                    'penulis',
                    'jumlah'
                ]);
            });
        })->export('xlsx');
    }

    public function importExcel(Request $request) {
        // Validasi untuk memastikan file yang diupload adalah excel
        $this->validate($request, ['excel' => 'required|mimes:xls,xlsx']);

        // Ambil file yang baru diupload
        $excel = $request->file('excel');

        // Baca sheet pertama
        $excels = Excel::selectSheetsByIndex(0)->load($excel, function($reader) {
            // Options, jika ada
        })->get();

        // Rule untuk validasi setiap row pada file excel
        $rowRules = [
            'judul'     => 'required',
            'penulis'   => 'required',
            'jumlah'    => 'required'
        ];

        // Catat semua id buku baru
        // ID ini kita butuhkan untuk menghitung total buku yang berhasil diimport
        $books_id = [];

        // Looping setiap baris, mulai dari baris ke 2 (karena baris ke 1 adalah nama kolom)
        foreach ($excels as $row) {
            // Membuat validasi untuk row di excel
            // Disini kita ubah baris yang sedang diproses menjadi array
            $validator = Validator::make($row->toArray(), $rowRules);

            // Skip baris ini jika tidak valid, langsung ke baris selanjutnya
            if ($validator->fails()) continue;

            // Syntax dibawah dieksekusi jika baris excel ini valid

            // Cek apakah Penulis sudah terdaftar di database
            $author = Author::where('name', $row['penulis'])->first();

            // Buat penulis jika belum ada
            if (!$author) {
                $author = Author::create(['name' => $row['penulis']]);
            }

            // Buat buku baru
            $book = Book::create([
                'title'     => $row['judul'],
                'author_id' => $author->id,
                'amount'    => $row['jumlah']
            ]);

            // Catat id dari buku yang baru dibuat
            array_push($books_id, $book->id);
        }

        // Ambil semua buku yang baru dibuat
        $books = Book::whereIn('id', $books_id)->get();

        // Redirect ke form jika tidak ada buku yang berhasil diimport
        if ($books->count() == 0) {
            Session::flash("flash_notification", [
                "level" => "danger",
                "message" => "Tidak ada buku yang berhasil diimport."
            ]);
            return redirect()->back();
        }

        // Set feedback
        Session::flash("flash_notification", [
            "level" => "success",
            "message" => "Berhasil mengimport " . $books->count() . " buku."
        ]);

        // Tampilkan index buku
        return view('books.import-review')->with(compact('books'));
    }

}

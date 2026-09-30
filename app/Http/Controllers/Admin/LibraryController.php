<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\User;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.library.books');
    }

    public function books()
    {
        $books = Book::all();
        return view('admin.library.books', compact('books'));
    }

    public function issues()
    {
        $books = Book::all();
        $issues = BookIssue::with(['book', 'user'])->orderBy('issue_date', 'desc')->get();
        $users = User::with(['role', 'studentProfile', 'staffProfile'])
            ->whereHas('role', function($q) {
                $q->whereNotIn('name', ['parent']);
            })->get();

        return view('admin.library.issues', compact('books', 'issues', 'users'));
    }

    public function storeBook(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'author' => 'required|string',
            'isbn' => 'required|string|unique:books,isbn',
            'publisher' => 'nullable|string',
            'rack_no' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $qty = $request->quantity;
        Book::create([
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'publisher' => $request->publisher,
            'rack_no' => $request->rack_no,
            'quantity' => $qty,
            'available_qty' => $qty,
        ]);

        return back()->with('success', 'Book added to catalog successfully!');
    }

    public function issueBook(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'user_id' => 'required|exists:users,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
        ]);

        $book = Book::findOrFail($request->book_id);
        if ($book->available_qty <= 0) {
            return back()->with('error', 'Book is currently not available for issue.');
        }

        \DB::transaction(function () use ($request, $book) {
            BookIssue::create([
                'book_id' => $request->book_id,
                'user_id' => $request->user_id,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'status' => 'issued',
            ]);

            $book->decrement('available_qty');
        });

        return back()->with('success', 'Book issued successfully!');
    }

    public function returnBook(Request $request, $id)
    {
        $issue = BookIssue::findOrFail($id);
        if ($issue->status === 'returned') {
            return back()->with('error', 'Book already returned.');
        }

        $request->validate([
            'return_date' => 'required|date',
            'fine_amount' => 'required|numeric|min:0',
        ]);

        \DB::transaction(function () use ($request, $issue) {
            $issue->update([
                'return_date' => $request->return_date,
                'fine_amount' => $request->fine_amount,
                'status' => 'returned',
            ]);

            $issue->book->increment('available_qty');
        });

        return back()->with('success', 'Book return recorded successfully!');
    }
}

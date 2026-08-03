<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $term = $request->string('q')->trim();
        $messages = ContactMessage::query()
            ->when($term->isNotEmpty(), fn ($query) => $query->where(function ($search) use ($term): void {
                $value = '%'.$term.'%';
                $search->where('name', 'like', $value)
                    ->orWhere('email', 'like', $value)
                    ->orWhere('subject', 'like', $value)
                    ->orWhere('message', 'like', $value);
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.messages.index', compact('messages'));
    }

    public function show(ContactMessage $message)
    {
        if (! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

return view('admin.messages.show', compact('message'));
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Pesan berhasil dihapus.');
    }
}

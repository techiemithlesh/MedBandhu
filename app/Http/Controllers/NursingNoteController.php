<?php

namespace App\Http\Controllers;

use App\Models\IpdAdmission;
use App\Models\NursingNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NursingNoteController extends Controller
{
    public function store(Request $request, IpdAdmission $admission): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', Rule::in(NursingNote::CATEGORIES)],
            'note' => ['required', 'string', 'max:2000'],
        ]);

        $admission->nursingNotes()->create([
            'patient_id' => $admission->patient_id,
            'category' => $data['category'],
            'note' => $data['note'],
        ]);

        return back()->with('status', 'Note added.');
    }

    public function destroy(IpdAdmission $admission, NursingNote $note): RedirectResponse
    {
        abort_unless($note->ipd_admission_id === $admission->id, 404);
        abort_unless($note->recorded_by === auth()->id(), 403);

        $note->delete();

        return back()->with('status', 'Note removed.');
    }
}

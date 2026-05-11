<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Service;
use App\Models\Guardian;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('guardian')->latest()->paginate(10);
        return view('students.index', compact('students'));
    }

    public function create()
    {
        $guardians = Guardian::all();
        $services = Service::all();
        return view('students.create', compact('guardians', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_code' => 'required|string|unique:students',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students',
            'class' => 'required|string|max:50',
            'academic_year' => 'required|string|max:20',
            'guardian_id' => 'required|exists:guardians,id',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
        ]);

        $student = Student::create([
            'student_code' => $validated['student_code'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'class' => $validated['class'],
            'academic_year' => $validated['academic_year'],
            'guardian_id' => $validated['guardian_id'],
        ]);

        if ($request->filled('services')) {

            $student->services()->attach(
                $request->services
            );

        }

        return redirect()
            ->route('students.index')
            ->with('success','Estudante criado com sucesso.');
    }

    public function show(Student $student)
    {
        $student->load(['guardian', 'invoices.payments']);
        return view('students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $guardians = Guardian::all();
        return view('students.edit', compact('student', 'guardians'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_code' => 'required|string|unique:students,student_code,' . $student->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email,' . $student->id,
            'class' => 'required|string|max:50',
            'academic_year' => 'required|string|max:20',
            'guardian_id' => 'required|exists:guardians,id',
            'transport_required' => 'boolean',
        ]);

        // garante valor correto do checkbox
        $validated['transport_required'] = $request->has('transport_required');

        $student->update($validated);

        // sincronizar serviços (RELACIONAMENTO CORRETO)
        $student->services()->sync($request->services ?? []);

        return redirect()
            ->route('students.index')
            ->with('success', 'Estudante atualizado com sucesso.');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Estudante eliminado com sucesso.');
    }
}
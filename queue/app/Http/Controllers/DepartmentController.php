<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Str;

class DepartmentController extends Controller
{
    public function index()
    {
        return Department::all();
    }

    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string',
        ]);

        // Create the department
        $department = Department::create($request->all());

        // Generate the QR code for the newly created department
        $qrCode = $this->generateQRCode($department->id);

        // Update the department with the QR code path
        $department->update(['qr_code' => $qrCode]);

        // Return the created department along with QR code information
        return response()->json([
            'department' => $department,
            'qr_code' => $qrCode ? asset($qrCode) : null,
        ], Response::HTTP_CREATED);
    }

    public function generateQRCode($departmentId)
    {
        try {
            // Make a request to the GOQR API to generate QR code
            $response = Http::get('https://api.qrserver.com/v1/create-qr-code/', [
                'size' => '400x400',
                'data' => $departmentId,
            ]);
    
            // Check if the request was successful
            if ($response->successful()) {
                // Save the QR code image to a file or return the image data
                $jpgData = $response->body();
    
                // Define the file path
                $directory = public_path('qr_codes');
                $filePath = $directory . '/department_' . $departmentId . '.jpg';
    
                // Ensure the directory exists
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0755, true);
                }
    
                // Save the QR code image to a file
                File::put($filePath, $jpgData);
    
                // Log the file path for debugging
                Log::info('QR code saved to: ' . $filePath);
    
                // Return the relative file path
                return 'qr_codes/department_' . $departmentId . '.jpg';
            } else {
                // Log error if the request was not successful
                Log::error('Failed to generate QR code for department ' . $departmentId . '. Response: ' . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            // Log exception
            Log::error('Exception occurred while generating QR code: ' . $e->getMessage());
            return null;
        }
    }
    

    public function delete($id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json(['message' => 'Department not found'], Response::HTTP_NOT_FOUND);
        }

        $department->delete();

        return response()->json(['message' => 'Department deleted successfully'], Response::HTTP_OK);
    }

}

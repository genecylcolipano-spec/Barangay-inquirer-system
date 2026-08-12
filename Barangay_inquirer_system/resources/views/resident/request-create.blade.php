@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>📝 Submit New Request</h1>
            <p class="date-time">Fill out the form to request a document</p>
        </div>
    </div>

    <div class="content-grid" style="grid-template-columns: 1fr; max-width: 700px;">
        <div class="card">
            @if ($errors->any())
            <div style="background: #ffe0e0; border: 1px solid #ffb3b3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <p style="color: #c0392b; font-weight: 600; margin: 0;">Please fix the following errors:</p>
                <ul style="color: #c0392b; margin-top: 10px; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('resident.request.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <p style="color: #7f8c8d; font-size: 0.9em; margin-bottom: 30px;">
                    <i class="fas fa-info-circle"></i> Fields marked with <strong>*</strong> are required. You can track your request status in <strong>My Requests</strong>.
                </p>

                <!-- STEP 1: Choose Document Type -->
                <div style="margin-bottom: 35px; padding-bottom: 25px; border-bottom: 2px solid #ecf0f5;">
                    <h3 style="color: #2c3e50; font-weight: 600; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                        <span style="background: #667eea; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85em;">1</span>
                        Choose Document Type
                    </h3>
                    
                    <select name="document_type" class="form-control" required>
                        <option value="">-- Select a document --</option>
                        <option value="barangay_clearance" {{ old('document_type') === 'barangay_clearance' ? 'selected' : '' }}>Barangay Clearance</option>
                        <option value="purok_clearance" {{ old('document_type') === 'purok_clearance' ? 'selected' : '' }}>Purok Clearance</option>
                        <option value="business_permit_clearance" {{ old('document_type') === 'business_permit_clearance' ? 'selected' : '' }}>Business Permit Clearance</option>
                        <option value="certificate_of_indigency" {{ old('document_type') === 'certificate_of_indigency' ? 'selected' : '' }}>Certificate of Indigency</option>
                        <option value="residency_certificate" {{ old('document_type') === 'residency_certificate' ? 'selected' : '' }}>Residency Certificate</option>
                        <option value="cedula" {{ old('document_type') === 'cedula' ? 'selected' : '' }}>Cedula</option>
                        <option value="other" {{ old('document_type') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('document_type')
                        <small style="color: #c0392b; display:block; margin-top:8px;"><i class="fas fa-exclamation-circle"></i> {{ $message }}</small>
                    @enderror
                </div>

                <!-- STEP 2: Fill Out Required Details -->
                <div style="margin-bottom: 35px; padding-bottom: 25px; border-bottom: 2px solid #ecf0f5;">
                    <h3 style="color: #2c3e50; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <span style="background: #667eea; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85em;">2</span>
                        Fill Out Required Details
                    </h3>

                    <!-- Full Name -->
                    <div style="margin-bottom: 20px;">
                        <label style="color: #2c3e50; font-weight: 600; display: block; margin-bottom: 8px;">
                            Full Name *
                        </label>
                        <input type="text" name="full_name" class="form-control" placeholder="Enter your full name" value="{{ old('full_name', auth()->user()->name ?? '') }}" required>
                        @error('full_name')
                            <small style="color: #c0392b; display:block; margin-top:5px;"><i class="fas fa-exclamation-circle"></i> {{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Address -->
                    <div style="margin-bottom: 20px;">
                        <label style="color: #2c3e50; font-weight: 600; display: block; margin-bottom: 8px;">
                            Address *
                        </label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Enter your complete residential address" required style="resize: vertical;">{{ old('address') }}</textarea>
                        @error('address')
                            <small style="color: #c0392b; display:block; margin-top:5px;"><i class="fas fa-exclamation-circle"></i> {{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Purpose -->
                    <div style="margin-bottom: 0;">
                        <label style="color: #2c3e50; font-weight: 600; display: block; margin-bottom: 8px;">
                            Purpose *
                        </label>
                        <textarea name="details" class="form-control" rows="3" placeholder="Why do you need this document? (e.g., Employment, Travel, Application, Business License, etc.)" required style="resize: vertical;">{{ old('details') }}</textarea>
                        <small style="color: #7f8c8d; display: block; margin-top: 5px;"><i class="fas fa-lightbulb"></i> Provide a clear explanation of your specific need.</small>
                        @error('details')
                            <small style="color: #c0392b; display:block; margin-top:5px;"><i class="fas fa-exclamation-circle"></i> {{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <!-- STEP 3: Upload ID -->
                <div style="margin-bottom: 30px;">
                    <h3 style="color: #2c3e50; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <span style="background: #667eea; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85em;">3</span>
                        Upload ID
                    </h3>

                    <label style="color: #2c3e50; font-weight: 600; display: block; margin-bottom: 12px;">
                        ID Document *
                    </label>
                    
                    <div style="border: 2px dashed #667eea; border-radius: 12px; padding: 35px; text-align: center; cursor: pointer; transition: all 0.3s ease; background: #f8f9ff;" 
                         onmouseover="this.style.background='#f0f1ff'; this.style.borderColor='#764ba2';" 
                         onmouseout="this.style.background='#f8f9ff'; this.style.borderColor='#667eea';"
                         onclick="document.getElementById('file-input').click();">
                        <div style="font-size: 2.8em; margin-bottom: 12px;"><i class="fas fa-id-card"></i></div>
                        <p style="color: #2c3e50; font-weight: 600; margin-bottom: 5px;">Click to upload or drag and drop</p>
                        <p style="color: #7f8c8d; font-size: 0.9em; margin: 0;">PNG, JPG, PDF up to 10MB</p>
                        <p style="color: #667eea; font-size: 0.85em; margin-top: 8px;"><i class="fas fa-info-circle"></i> Accepted: Valid ID, Drivers License, Passport</p>
                        <input type="file" id="file-input" name="attachment" class="form-control" style="display: none;" onchange="updateFileName(this)" required>
                    </div>
                    <p id="file-name" style="color: #27ae60; font-size: 0.9em; margin-top: 12px; text-align: center; font-weight: 600;"></p>
                    @error('attachment')
                        <small style="color: #c0392b; display:block; margin-top:8px; text-align:center;"><i class="fas fa-exclamation-circle"></i> {{ $message }}</small>
                    @enderror
                </div>

                <!-- Buttons -->
                <div style="display: flex; gap: 15px; margin-top: 30px; border-top: 1px solid #ecf0f5; padding-top: 20px;">
                    <button type="submit" class="btn btn-primary">✅ Submit Request</button>
                    <a href="{{ route('resident.dashboard') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.greeting h1 {
    font-size: 2.5em;
    color: #2c3e50;
    margin-bottom: 8px;
    font-weight: 600;
}

.date-time {
    color: #7f8c8d;
    font-size: 0.95em;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.form-control {
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 12px 15px;
    font-family: inherit;
    font-size: 1em;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}

.btn {
    border: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    padding: 12px 28px;
    font-size: 0.95em;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    color: #2c3e50;
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.content-grid {
    display: grid;
    gap: 25px;
}

@media (max-width: 768px) {
    .greeting h1 {
        font-size: 1.8em;
    }

    .card {
        padding: 20px;
    }
}
</style>

<script>
    function updateFileName(input) {
        const fileName = input.files[0]?.name || '';
        document.getElementById('file-name').textContent = fileName ? '✓ ' + fileName + ' selected' : '';
    }
</script>
@endsection

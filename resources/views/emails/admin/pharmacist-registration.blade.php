<!DOCTYPE html>
<html>
<head>
    <title>New Pharmacist Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            margin-top: 20px;
        }
        .details {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .approve-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
        }
        .reject-button {
            background-color: #f44336;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 0.9em;
        }
        .document-link {
            display: inline-block;
            background-color: #2196F3;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px 0;
        }
        .verification-link {
            display: inline-block;
            background-color: #FF9800;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px 0;
        }
        .document-preview {
            margin: 10px 0;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .document-preview img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .action-form {
            margin: 20px 0;
            padding: 15px;
            background-color: #fff;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .action-form textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>New Pharmacist Registration - TIN Verification Required</h2>
    </div>
    
    <div class="content">
        <p>Hello Admin,</p>
        
        <p>A new pharmacist has registered on the platform and requires TIN verification.</p>
        
        <div class="details">
            <h3>Pharmacist Details:</h3>
            <ul>
                <li><strong>Name:</strong> {{ $pharmacist->name }}</li>
                <li><strong>Email:</strong> {{ $pharmacist->email }}</li>
                <li><strong>Pharmacy Name:</strong> {{ $pharmacist->pharmacy_name }}</li>
                <li><strong>Phone:</strong> {{ $pharmacist->phone }}</li>
                <li><strong>Address:</strong> {{ $pharmacist->address }}</li>
                <li><strong>TIN Number:</strong> {{ $pharmacist->tin_number }}</li>
            </ul>
            
            <h3>Documents:</h3>
            <div class="document-preview">
                <h4>License Document:</h4>
                <img src="{{ $licenseImageUrl }}" alt="License Document" style="max-width: 100%;">
                <p>
                    <a href="{{ $licenseImageUrl }}" class="document-link" target="_blank">
                        View Full License Document
                    </a>
                </p>
            </div>

            <div class="document-preview">
                <h4>TIN Document:</h4>
                <img src="{{ $tinImageUrl }}" alt="TIN Document" style="max-width: 100%;">
                <p>
                    <a href="{{ $tinImageUrl }}" class="document-link" target="_blank">
                        View Full TIN Document
                    </a>
                </p>
            </div>
            
            <h3>Bank Details:</h3>
            <ul>
                <li><strong>Account Number:</strong> {{ $pharmacist->account_number }}</li>
                <li><strong>Bank Name:</strong> {{ $pharmacist->bank_name }}</li>
            </ul>
        </div>
        
        <p>Please verify the TIN number using the official verification link:</p>
        <p>
            <a href="{{ $tinVerificationUrl }}" class="verification-link" target="_blank">
                Verify TIN Number
            </a>
        </p>

        <div class="action-form">
            <h3>Take Action:</h3>
            <form action="{{ url('/api/admin/pharmacists/' . $pharmacist->id . '/status') }}" method="POST">
                @csrf
                <textarea name="reason" placeholder="Enter reason for approval or rejection (optional)" rows="3"></textarea>
                <div class="action-buttons">
                    <button type="submit" name="action" value="approve" class="approve-button">
                        Approve Registration
                    </button>
                    <button type="submit" name="action" value="reject" class="reject-button">
                        Reject Registration
                    </button>
                </div>
            </form>
        </div>
        
        <p>Thank you for using our application!</p>
    </div>
    
    <div class="footer">
        <p>Best regards,<br>EPharmacy System</p>
    </div>
</body>
</html> 
@component('mail::message')
# New Pharmacist Registration - TIN Verification Required

Hello Admin,

A new pharmacist has registered on the platform and requires TIN verification.

## Pharmacist Details:
- **Name:** {{ $pharmacist->name }}
- **Email:** {{ $pharmacist->email }}
- **Pharmacy Name:** {{ $pharmacist->pharmacy_name }}
- **Phone:** {{ $pharmacist->phone }}
- **Address:** {{ $pharmacist->address }}
- **TIN Number:** {{ $pharmacist->tin_number }}

## Documents:
@if($pharmacist->license_image)
### License Document:
![License Document]({{ $pharmacist->license_image }})
@endif

@if($pharmacist->tin_document)
### TIN Document:
![TIN Document]({{ $pharmacist->tin_document }})
@endif

## Bank Details:
- **Account Number:** {{ $pharmacist->account_number }}
- **Bank Name:** {{ $pharmacist->bank_name }}

Please verify the TIN number using the official verification link:
@component('mail::button', ['url' => 'https://etrade.gov.et/business-license-checker?tin=' . $pharmacist->tin_number])
Verify TIN Number
@endcomponent

## Take Action:
@component('mail::button', ['url' => url('/api/admin/pharmacists/' . $pharmacist->id . '/status?action=approve'), 'color' => 'success'])
Approve Registration
@endcomponent

@component('mail::button', ['url' => url('/api/admin/pharmacists/' . $pharmacist->id . '/status?action=reject'), 'color' => 'error'])
Reject Registration
@endcomponent

Thank you for using our application!

Best regards,<br>
EPharmacy System
@endcomponent 
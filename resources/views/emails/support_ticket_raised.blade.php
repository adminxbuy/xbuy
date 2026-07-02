<!DOCTYPE html>
<html>
<head>
    <title>New Support Ticket Raised</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Hello Administrator,</h2>
    <p>A new support ticket has been raised by a buyer on x-buy.</p>
    
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; width: 150px;">Buyer Name:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $buyerName }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Buyer Email:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $buyerEmail }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Subject:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $ticket->subject }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Message:</td>
            <td style="padding: 8px; border: 1px solid #ddd; white-space: pre-wrap;">{{ $ticket->message }}</td>
        </tr>
    </table>

    <p>Please check the admin dashboard to manage and resolve this ticket.</p>
    
    <p>Thank you,<br>Support Ticket System</p>
</body>
</html>

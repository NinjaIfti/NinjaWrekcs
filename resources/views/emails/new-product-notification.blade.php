<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Product Available - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #18181b;
            font-size: 24px;
            margin-top: 0;
        }
        h2 {
            color: #18181b;
            font-size: 20px;
            margin-top: 20px;
        }
        p {
            font-size: 16px;
            line-height: 1.5;
            color: #52525b;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #8b5cf6;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #7c3aed;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <h1>New Product Available! ✨</h1>
        
        <p>We're excited to announce a new product in our collection!</p>
        
        <h2>{{ $product->name }}</h2>
        
        @if($product->description)
        <p>{{ Str::limit($product->description, 200) }}</p>
        @endif
        
        <p><strong>Price:</strong> ৳{{ number_format($product->display_price ?? $product->price, 2) }}</p>
        
        <div style="text-align: center;">
            <a href="{{ route('shop.show', $product) }}" class="button">View Product</a>
        </div>
        
        <p>Thanks,<br>{{ config('app.name') }}</p>
        
        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

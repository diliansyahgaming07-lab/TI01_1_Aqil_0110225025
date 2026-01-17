import json
import os
from django.shortcuts import render
from django.conf import settings

def index(request):
    products_file = os.path.join(settings.DATA_DIR, 'products.json')

    try:
        with open(products_file, 'r', encoding='utf-8') as f:
            products = json.load(f)
    except (FileNotFoundError, json.JSONDecodeError):
        products = []

    for i, product in enumerate(products):
        price = int(product.get('price', 0))
        # Format Rupiah yang benar di backend
        product['price_formatted'] = f"Rp {price:,}".replace(',', '.')
        
        # Tambah delay animasi
        delay = 0.1 + (0.1 * (i % 3))
        product['animation_delay'] = f"{delay:.1f}"

    return render(request, 'products/index.html', {
        'products': products
    })
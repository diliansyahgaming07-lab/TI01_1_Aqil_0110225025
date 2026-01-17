import json
import os
from datetime import datetime
from django.shortcuts import render, redirect
from django.conf import settings
from django.contrib import messages

def order_form(request, product_id):
    # Baca data produk
    products_file = os.path.join(settings.DATA_DIR, 'products.json')
    with open(products_file, 'r', encoding='utf-8') as f:
        products = json.load(f)
    
    # Cari produk berdasarkan ID
    product = None
    for p in products:
        if p['id'] == product_id:
            product = p
            break
    
    if request.method == 'POST':
        # Ambil data dari form
        order_data = {
            'id': int(datetime.now().timestamp()),
            'product_id': product_id,
            'product_name': product['name'],
            'product_price': product['price'],
            'customer_name': request.POST.get('customer_name'),
            'phone': request.POST.get('phone'),
            'address': request.POST.get('address'),
            'quantity': int(request.POST.get('quantity', 1)),
            'total_price': product['price'] * int(request.POST.get('quantity', 1)),
            'status': 'Baru',
            'order_date': datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        }
        
        # Simpan ke JSON
        orders_file = os.path.join(settings.DATA_DIR, 'orders.json')
        
        # Baca orders yang ada
        if os.path.exists(orders_file):
            with open(orders_file, 'r', encoding='utf-8') as f:
                try:
                    orders = json.load(f)
                except:
                    orders = []
        else:
            orders = []
        
        # Tambahkan order baru
        orders.append(order_data)
        
        # Simpan kembali
        with open(orders_file, 'w', encoding='utf-8') as f:
            json.dump(orders, f, indent=2, ensure_ascii=False)
        
        messages.success(request, 'Pesanan berhasil dibuat! Kami akan segera menghubungi Anda.')
        return redirect('products:index')
    
    context = {
        'product': product
    }
    return render(request, 'orders/order_form.html', context)
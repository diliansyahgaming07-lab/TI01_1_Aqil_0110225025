import json
import os
from django.shortcuts import render, redirect
from django.conf import settings
from django.contrib import messages

# Hardcoded admin credentials
ADMIN_USERNAME = 'admin'
ADMIN_PASSWORD = 'admin123'

def login(request):
    if request.method == 'POST':
        username = request.POST.get('username')
        password = request.POST.get('password')
        
        if username == ADMIN_USERNAME and password == ADMIN_PASSWORD:
            request.session['is_admin'] = True
            return redirect('adminpanel:dashboard')
        else:
            messages.error(request, 'Username atau password salah!')
    
    return render(request, 'adminpanel/login.html')

def logout(request):
    request.session.flush()
    return redirect('adminpanel:login')

def dashboard(request):
    # Cek apakah sudah login
    if not request.session.get('is_admin'):
        return redirect('adminpanel:login')
    
    # Baca data orders
    orders_file = os.path.join(settings.DATA_DIR, 'orders.json')
    
    if os.path.exists(orders_file):
        with open(orders_file, 'r', encoding='utf-8') as f:
            try:
                orders = json.load(f)
            except:
                orders = []
    else:
        orders = []
    
    # Urutkan berdasarkan tanggal terbaru
    orders = sorted(orders, key=lambda x: x['order_date'], reverse=True)
    
    # Ambil 5 pesanan terbaru untuk dashboard
    recent_orders = orders[:5]
    
    context = {
        'orders': recent_orders,
        'total_orders': len(orders),
        'new_orders': len([o for o in orders if o['status'] == 'Baru']),
        'processing_orders': len([o for o in orders if o['status'] == 'Diproses']),
        'completed_orders': len([o for o in orders if o['status'] == 'Selesai']),
    }
    return render(request, 'adminpanel/dashboard.html', context)

def orders_list(request):
    # Cek apakah sudah login
    if not request.session.get('is_admin'):
        return redirect('adminpanel:login')
    
    # Baca data orders
    orders_file = os.path.join(settings.DATA_DIR, 'orders.json')
    
    if os.path.exists(orders_file):
        with open(orders_file, 'r', encoding='utf-8') as f:
            try:
                orders = json.load(f)
            except:
                orders = []
    else:
        orders = []
    
    # Filter berdasarkan status jika ada
    status_filter = request.GET.get('status', '')
    if status_filter:
        orders = [o for o in orders if o['status'] == status_filter]
    
    # Urutkan berdasarkan tanggal terbaru
    orders = sorted(orders, key=lambda x: x['order_date'], reverse=True)
    
    context = {
        'orders': orders,
        'status_filter': status_filter,
        'total_orders': len(orders),
    }
    return render(request, 'adminpanel/orders_list.html', context)

def order_detail(request, order_id):
    # Cek apakah sudah login
    if not request.session.get('is_admin'):
        return redirect('adminpanel:login')
    
    orders_file = os.path.join(settings.DATA_DIR, 'orders.json')
    
    with open(orders_file, 'r', encoding='utf-8') as f:
        orders = json.load(f)
    
    # Cari order
    order = None
    order_index = None
    for idx, o in enumerate(orders):
        if o['id'] == order_id:
            order = o
            order_index = idx
            break
    
    if request.method == 'POST':
        new_status = request.POST.get('status')
        orders[order_index]['status'] = new_status
        
        # Simpan kembali
        with open(orders_file, 'w', encoding='utf-8') as f:
            json.dump(orders, f, indent=2, ensure_ascii=False)
        
        messages.success(request, 'Status pesanan berhasil diupdate!')
        return redirect('adminpanel:order_detail', order_id=order_id)
    
    context = {
        'order': order
    }
    return render(request, 'adminpanel/order_detail.html', context)

def delete_order(request, order_id):
    # Cek apakah sudah login
    if not request.session.get('is_admin'):
        return redirect('adminpanel:login')
    
    orders_file = os.path.join(settings.DATA_DIR, 'orders.json')
    
    with open(orders_file, 'r', encoding='utf-8') as f:
        orders = json.load(f)
    
    # Hapus order
    orders = [o for o in orders if o['id'] != order_id]
    
    # Simpan kembali
    with open(orders_file, 'w', encoding='utf-8') as f:
        json.dump(orders, f, indent=2, ensure_ascii=False)
    
    messages.success(request, 'Pesanan berhasil dihapus!')
    return redirect('adminpanel:orders_list')

# PRODUCTS MANAGEMENT
def products_list(request):
    # Cek apakah sudah login
    if not request.session.get('is_admin'):
        return redirect('adminpanel:login')
    
    # Baca data products
    products_file = os.path.join(settings.DATA_DIR, 'products.json')
    
    with open(products_file, 'r', encoding='utf-8') as f:
        products = json.load(f)
    
    context = {
        'products': products,
        'total_products': len(products),
    }
    return render(request, 'adminpanel/products_list.html', context)

def product_create(request):
    # Cek apakah sudah login
    if not request.session.get('is_admin'):
        return redirect('adminpanel:login')
    
    if request.method == 'POST':
        products_file = os.path.join(settings.DATA_DIR, 'products.json')
        
        with open(products_file, 'r', encoding='utf-8') as f:
            products = json.load(f)
        
        # Generate ID baru
        new_id = max([p['id'] for p in products]) + 1 if products else 1
        
        # Buat product baru
        new_product = {
            'id': new_id,
            'name': request.POST.get('name'),
            'price': int(request.POST.get('price')),
            'description': request.POST.get('description'),
            'category': request.POST.get('category'),
            'image': request.POST.get('image'),
        }
        
        products.append(new_product)
        
        # Simpan kembali
        with open(products_file, 'w', encoding='utf-8') as f:
            json.dump(products, f, indent=2, ensure_ascii=False)
        
        messages.success(request, 'Produk berhasil ditambahkan!')
        return redirect('adminpanel:products_list')
    
    return render(request, 'adminpanel/product_form.html', {'action': 'create'})

def product_edit(request, product_id):
    # Cek apakah sudah login
    if not request.session.get('is_admin'):
        return redirect('adminpanel:login')
    
    products_file = os.path.join(settings.DATA_DIR, 'products.json')
    
    with open(products_file, 'r', encoding='utf-8') as f:
        products = json.load(f)
    
    # Cari product
    product = None
    product_index = None
    for idx, p in enumerate(products):
        if p['id'] == product_id:
            product = p
            product_index = idx
            break
    
    if request.method == 'POST':
        products[product_index]['name'] = request.POST.get('name')
        products[product_index]['price'] = int(request.POST.get('price'))
        products[product_index]['description'] = request.POST.get('description')
        products[product_index]['category'] = request.POST.get('category')
        products[product_index]['image'] = request.POST.get('image')
        
        # Simpan kembali
        with open(products_file, 'w', encoding='utf-8') as f:
            json.dump(products, f, indent=2, ensure_ascii=False)
        
        messages.success(request, 'Produk berhasil diupdate!')
        return redirect('adminpanel:products_list')
    
    context = {
        'product': product,
        'action': 'edit'
    }
    return render(request, 'adminpanel/product_form.html', context)

def product_delete(request, product_id):
    # Cek apakah sudah login
    if not request.session.get('is_admin'):
        return redirect('adminpanel:login')
    
    products_file = os.path.join(settings.DATA_DIR, 'products.json')
    
    with open(products_file, 'r', encoding='utf-8') as f:
        products = json.load(f)
    
    # Hapus product
    products = [p for p in products if p['id'] != product_id]
    
    # Simpan kembali
    with open(products_file, 'w', encoding='utf-8') as f:
        json.dump(products, f, indent=2, ensure_ascii=False)
    
    messages.success(request, 'Produk berhasil dihapus!')
    return redirect('adminpanel:products_list')
from django.urls import path
from . import views

app_name = 'orders'

urlpatterns = [
    path('form/<int:product_id>/', views.order_form, name='order_form'),
]
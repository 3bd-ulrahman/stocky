<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert some stuff
        DB::table('permissions')->insert([
            [
                'name' => 'users_view',
            ],
            [
                'name' => 'users_edit',
            ],
            [
                'name' => 'record_view',
            ],
            [
                'name' => 'users_delete',
            ],
            [
                'name' => 'users_add',
            ],
            [
                'name' => 'permissions_edit',
            ],
            [
                'name' => 'permissions_view',
            ],
            [
                'name' => 'permissions_delete',
            ],
            [
                'name' => 'permissions_add',
            ],
            [
                'name' => 'products_delete',
            ],
            [
                'name' => 'products_view',
            ],
            [
                'name' => 'barcode_view',
            ],
            [
                'name' => 'products_edit',
            ],
            [
                'name' => 'products_add',
            ],
            [
                'name' => 'expense_add',
            ],
            [
                'name' => 'expense_delete',
            ],
            [
                'name' => 'expense_edit',
            ],
            [
                'name' => 'expense_view',
            ],
            [
                'name' => 'transfer_delete',
            ],
            [
                'name' => 'transfer_add',
            ],
            [
                'name' => 'transfer_view',
            ],
            [
                'name' => 'transfer_edit',
            ],
            [
                'name' => 'adjustment_delete',
            ],
            [
                'name' => 'adjustment_add',
            ],
            [
                'name' => 'adjustment_edit',
            ],
            [
                'name' => 'adjustment_view',
            ],
            [
                'name' => 'Sales_edit',
            ],
            [
                'name' => 'Sales_view',
            ],
            [
                'name' => 'Sales_delete',
            ],
            [
                'name' => 'Sales_add',
            ],
            [
                'name' => 'Purchases_edit',
            ],
            [
                'name' => 'Purchases_view',
            ],
            [
                'name' => 'Purchases_delete',
            ],
            [
                'name' => 'Purchases_add',
            ],
            [
                'name' => 'Quotations_edit',
            ],
            [
                'name' => 'Quotations_delete',
            ],
            [
                'name' => 'Quotations_add',
            ],
            [
                'name' => 'Quotations_view',
            ],
            [
                'name' => 'payment_sales_delete',
            ],
            [
                'name' => 'payment_sales_add',
            ],
            [
                'name' => 'payment_sales_edit',
            ],
            [
                'name' => 'payment_sales_view',
            ],
            [
                'name' => 'Purchase_Returns_delete',
            ],
            [
                'name' => 'Purchase_Returns_add',
            ],
            [
                'name' => 'Purchase_Returns_view',
            ],
            [
                'name' => 'Purchase_Returns_edit',
            ],
            [
                'name' => 'Sale_Returns_delete',
            ],
            [
                'name' => 'Sale_Returns_add',
            ],
            [
                'name' => 'Sale_Returns_edit',
            ],
            [
                'name' => 'Sale_Returns_view',
            ],
            [
                'name' => 'payment_purchases_edit',
            ],
            [
                'name' => 'payment_purchases_view',
            ],
            [
                'name' => 'payment_purchases_delete',
            ],
            [
                'name' => 'payment_purchases_add',
            ],
            [
                'name' => 'payment_returns_edit',
            ],
            [
                'name' => 'payment_returns_view',
            ],
            [
                'name' => 'payment_returns_delete',
            ],
            [
                'name' => 'payment_returns_add',
            ],
            [
                'name' => 'Customers_edit',
            ],
            [
                'name' => 'Customers_view',
            ],
            [
                'name' => 'Customers_delete',
            ],
            [
                'name' => 'Customers_add',
            ],
            [
                'name' => 'unit',
            ],
            [
                'name' => 'currency',
            ],
            [
                'name' => 'category',
            ],
            [
                'name' => 'backup',
            ],
            [
                'name' => 'warehouse',
            ],
            [
                'name' => 'brand',
            ],
            [
                'name' => 'setting_system',
            ],
            [
                'name' => 'Warehouse_report',
            ],
            [
                'name' => 'Reports_quantity_alerts',
            ],
            [
                'name' => 'Reports_profit',
            ],
            [
                'name' => 'Reports_suppliers',
            ],
            [
                'name' => 'Reports_customers',
            ],
            [
                'name' => 'Reports_purchase',
            ],
            [
                'name' => 'Reports_sales',
            ],
            [
                'name' => 'Reports_payments_purchase_Return',
            ],
            [
                'name' => 'Reports_payments_Sale_Returns',
            ],
            [
                'name' => 'Reports_payments_Purchases',
            ],
            [
                'name' => 'Reports_payments_Sales',
            ],
            [
                'name' => 'Suppliers_delete',
            ],
            [
                'name' => 'Suppliers_add',
            ],
            [
                'name' => 'Suppliers_edit',
            ],
            [
                'name' => 'Suppliers_view',
            ],
            [
                'name' => 'Pos_view',
            ],
            [
                'name' => 'product_import',
            ],
            [
                'name' => 'customers_import',
            ],
            [
                'name' => 'Suppliers_import',
            ],

            //hrm
            [
                'name' => 'view_employee',
            ],
            [
                'name' => 'add_employee',
            ],
            [
                'name' => 'edit_employee',
            ],
            [
                'name' => 'delete_employee',
            ],
            [
                'name' => 'company',
            ],
            [
                'name' => 'department',
            ],
            [
                'name' => 'designation',
            ],
            [
                'name' => 'office_shift',
            ],
            [
                'name' => 'attendance',
            ],
            [
                'name' => 'leave',
            ],
            [
                'name' => 'holiday',
            ],
            [
                'name' => 'Top_products',
            ],
            [
                'name' => 'Top_customers',
            ],
            [
                'name' => 'shipment',
            ],
            [
                'name' => 'users_report',
            ],
            [
                'name' => 'stock_report',
            ],
            [
                'name' => 'sms_settings',
            ],
            [
                'name' => 'pos_settings',
            ],
            [
                'name' => 'payment_gateway',
            ],
            [
                'name' => 'mail_settings',
            ],
            [
                'name' => 'dashboard',
            ],
            [
                'name' => 'pay_due',
            ],
            [
                'name' => 'pay_sale_return_due',
            ],
            [
                'name' => 'pay_supplier_due',
            ],
            [
                'name' => 'pay_purchase_return_due',
            ],
            [
                'name' => 'product_report',
            ],
            [
                'name' => 'product_sales_report',
            ],
            [
                'name' => 'product_purchases_report',
            ],
            [
                'name' => 'notification_template',
            ],
            [
                'name' => 'edit_product_sale',
            ],
            [
                'name' => 'edit_product_purchase',
            ],
            [
                'name' => 'edit_product_quotation',
            ],
            [
                'name' => 'edit_tax_discount_shipping_sale',
            ],
            [
                'name' => 'edit_tax_discount_shipping_purchase',
            ],
            [
                'name' => 'edit_tax_discount_shipping_quotation',
            ]
        ]);
    }
}

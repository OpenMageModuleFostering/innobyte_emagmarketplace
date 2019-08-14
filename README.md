eMAG Marketplace [1.0.0]
========================

### 1. Description
- This module integrantes eMAG Marketplace API into Magento.
- DEMO: http://demo.innobyte.com/emag_marketplace

### 2. Features
- Enable/Disable eMAG Marketplace module.
- Supports different eMAG vendors (per store).
- Import eMAG categories, VATs, localities (from `eMAG Marketplace` admin menu).
- New tab on product edit page available on store view level for saving eMAG product data.
- Entire product documentation sending or just offer sending.
- Autocomplete city available on order edit page / system config shipping settings  origin city.
- AWB generation through eMAG for an order (settings must be first set from `System` -> `Configuration` -> `eMAG Marketplace` -> `Shipping Settings`)

### 3. Compatible with
- Magento CE >= 1.7.0.2

### 4. Usage
1. Go to `System`->`Configuration`->`Innobyte Extensions`->`eMAG Marketplace` fill in the licence key and set extension options. Be aware that some/most of the options are available only on store view level.
2. Refresh your cache and verify if everything works as described (go to admin `eMAG Marketplace` menu and import vats, categories, localities, then go to catalog and send some products to eMAG. After some orders have been placed on eMAG platform, cron should read them and orders should appear also in Magento).


### 5. Technical specification
API Integration documentation can be found in `docs/` folder.
###### Rewrites
- Models
    - *customer_form*: remove eMAG attributes from forms if emag_order_id is not available
    - *sales_order_status*: added eventPrefix and eventObject properties
    - *sales_resource_order_item_collection*: added eventPrefix and eventObject properties
- Blocks
    - *adminhtml_catalog_product_edit*: added 3 more buttons on product edit page

###### Event observers
- *sales_quote_save_after*: save eMAG quote attributes to database
- *sales_order_save_after*: save eMAG order attributes to database
- *sales_order_creditmemo_save_after*: update eMAG order with storno invoice
- *sales_quote_load_after*: add eMAG quote attribute to quote collection
- *sales_order_load_after*: add eMAG order attribute to order collection
- *sales_order_invoice_load_after*: attach vouchers to invoice
- *sales_order_creditmemo_load_after*: attach vouchers to creditmemo
- *sales_quote_address_save_after*: save eMAG quote address attributes to database
- *sales_order_address_save_after*: save eMAG order address attributes to database
- *sales_quote_address_collection_load_after*: add eMAG quote address attribute to quote address collection
- *sales_order_address_collection_load_after*: add eMAG order address attribute to order address collection
- *core_copy_fieldset_customer_account_to_quote*: copy eMAG customer attributes to quote
- *core_copy_fieldset_customer_address_to_quote_address*: copy eMAG customer address attributes to quote address
- *core_copy_fieldset_sales_convert_quote_to_order*: copy eMAG attributes from quote to order
- *core_copy_fieldset_sales_convert_quote_address_to_order_address*: copy eMAG attributes from quote address to order address
- *core_copy_fieldset_sales_convert_quote_address_to_customer_address*: copy eMAG attributes from quote address to customer address
- *core_copy_fieldset_checkout_onepage_quote_to_customer*: copy eMAG attributes from quote to customer
- *core_copy_fieldset_sales_copy_order_to_edit*: copy eMAG attributes from original order to new order
- *core_copy_fieldset_sales_convert_order_to_invoice*: copy eMAG attributes from order to invoice
- *core_copy_fieldset_sales_convert_order_to_cm*: copy eMAG attributes from order to creditmemo
- *core_copy_fieldset_sales_copy_order_billing_address_to_order*: copy eMAG attributes from billing address to order
- *core_copy_fieldset_sales_copy_order_shipping_address_to_order*: copy eMAG attributes from shipping address to order
- *catalog_product_save_after*: saves eMAG specific product data.
- *adminhtml_catalog_product_grid_prepare_massaction*: add 3 more mass actions on products grid.

###### Cron jobs
- `innobyte_emag_marketplace_cron`: pull orders from eMAG API (runs every 5 minutes)

###### Dispatched events
- `innobyte_emag_marketplace_compute_api_product_data` is dispatched in *Innobyte_EmagMarketplace_Model_Api_Product* class in order to customize (if needed by clients) product data sent for a product to eMAG API.
- `innobyte_emag_marketplace_compute_api_offer_data` is dispatched in *Innobyte_EmagMarketplace_Model_Api_Product* class in order to customize (if needed by clients) product offer data sent for a product to eMAG API.
- `innobyte_emag_marketplace_prepare_emag_product_form` is dispatched in *Innobyte_EmagMarketplace_Block_Adminhtml_Catalog_Product_Edit_Tab_EmagMarketplace* class in order to customize (if needed by clients) eMAG product form fields.
- `innobyte_emag_marketplace_compute_api_awb_data` is dispatched in *Innobyte_EmagMarketplace_Model_Api_Awb* class in order to customize (if needed by clients) AWB data to be sent to eMAG API.

###### Api Settings:
- Admin->*System*->*Configuration*->*eMAG Marketplace*->*API Settings* (store view level):
    - api url: marketplace API URL
    - api username: vendor 's username
    - api password: vendor 's password
    - client code: vendor 's code

###### eMAG Statuses:
- Add 6 new statuses for eMAG and map the with corresponding magento states (emag_status[magento_state]):
    - emag_new[new]
    - emag_in_progress[processing]
    - emag_prepared[processing]
    - emag_finalized[processing]
    - emag_canceled[processing]
    - emag_returned[closed]

###### Vouchers:
- New tables:
	- innobyte_emag_marketplace_sales_flat_order_voucher
	- innobyte_emag_marketplace_sales_flat_quote_voucher  
Columns:
		- id: ID
		- entity_id: Entity Id, quote/order
		- emag_id: eMAG ID
		- emag_voucher_id: eMAG Voucher ID
		- emag_voucher_name: eMAG Voucher Name
		- emag_sale_price: eMAG Sale Price
		- base_emag_sale_price: eMAG Sale Price in base currency
		- emag_sale_price_vat: eMAG Sale Price VAT
		- base_emag_sale_price_vat: eMAG Sale Price VAT	in base currency
		- emag_status: eMAG Status
		- emag_vat: eMAG VAT
		- emag_created: eMAG Creation Time
		- emag_modified: eMAG Modification Time
- Added custom layout for admin to show eMAG vouchers in order/invoice/creditmemo totals.
- New PDF Total model to display voucher discount in invoice PDF.

###### Attibutes:
- Customer attributes: (visible in forms: adminhtml_customer)
	- emag_order_id
	- emag_customer_id
	- emag_customer_comment
	- emag_customer_gender
	- emag_payment_status
	- emag_order_date
- Customer address attributes: (visible in forms: adminhtml_customer_address)
	- emag_company_code
	- emag_company_reg_no
	- emag_bank
	- emag_iban
	- emag_legal_entity
	- emag_is_vat_payer
	- emag_telephone_2
	- emag_telephone_3
	- emag_locality_id

###### Invoice:
- Added system config field for invoice and creditmemo incement prefix. It updates increment_id column from eav_entity_store table. Can only be edited at store level.
- Added new button in admin order view page:
	- invoice upload
	- invoice download
	- invoice delete
- Added custom layout to modify invoice totals
- New model for <global><sales><order_invoice><totals> that handles vouchers and applies correct grand total

###### Refunds:
- New model for <global><sales><order_creditmemo><totals> that handles vouchers and applies correct grand total
- Added custom layout to modify creditmemo totals

###### Api:
- Models for each resource containing all 4 methods:
	- read
	- save
	- count
	- acknowledge  
For those resources that do not have the method implemented exception will be thrown.
- Api response model

###### Orders:
- Sync:
	- convert to magento order:
		- reserve increment id for new order and set eMAG order id, store id, date and status
		- apply global, base, store, order currency to order
		- prepare currency rates that will be applied to order
		- convert eMAG order products into magento order items and set order totals according to each product price
			- if product currency does not match store currency sync will fail
			- if product is canceled do not add it to magento order
			- if product could not be loaded(missing from magento or wrong id from eMAG) sync will fail
		- apply eMAG vouchers to magento order and recalculate totals based on voucher values
		- apply guest customer to magento order(set dummy email address as email is not provided by eMAG)
		- assign billing address
		- assign shipping address
		- apply shipping cost to magento order and recalculate totals
		- prepare payment method
		- apply customer comment to magento order
		- automatically generate order invoice if eMAG payment method is credit card and status is "paid" 
	- convert to eMAG order:
		- set eMAG order id, status, date
		- assign customer data
		- convert magento billing address to eMAG address
		- convert magento shipping address to eMAG address
		- convert order products to eMAG products
		- assign invoices/creditmemos to eMAG order
		- assign vouchers to eMAG order
		- apply payment
		- apply customer comment
		- add attachements
- Edit:
	- new model for <global><sales><quote><totals> that handles vouchers and applies correct grand total
	- added custom layout to modify order product grid in order to display product prices from original order
	- added custom layout to modify order totals
- View:
    - new model for <global><sales><quote><totals> that handles vouchers and applies correct grand total
    - added eMAG statuses buttons
    - added custom layout to add customer comments
    - added custom layout to modify order totals

###### Shipping:
New shipping method with custom price (visible only in admin)

###### Payment:
New payment methods added for eMAG(visible only in admin):
- bank transfer
- cash on delivery
- credit card
- unknown (used for exceptions, when eMAG payment is not available on eMAG order)

###### Catalog Product
- On edit page, on store view level, *eMAG Marketplace* tab should be available for simple & configurable products.
- Edit block is rewritten, also its template file has been changed (added 3 more buttons) from layout.
- On configurable products, an extra field 'Family Type' is present, it represents the modality of grouping some products (its associated products) on eMAG. When a configurable product is sent to eMAG all it 's associated products are sent with family type - the family type of parent configurable product, family name - the name of parent configurable product, family id - the magento id of the parent configurable product. Be aware that if you sent an associated product from it 's own edit page, no family will be set for it.

### 6. Install
- Copy files / folders (design/skin files should be put in the default theme of your current package)
- Refresh your cache, log out from admin and log back in.

# 4.5.4

- [#38941] Fixed bug in residual value calculation
- [#38943] Additionally to the createdAt field respect updatedAt field in transactions sort algorithm

# 4.5.3

- [#38860] Export/import profile expanded to include customer's number (see instructions)

# 4.5.2

- [#38660] Fixed a bug in the administration where an error was thrown when the customer has no vouchers.

# 4.5.1

- [#38644] Link to plugin configuration did not work
- [#38625] Fixed compatibility with SW 6.4.5.0
- [#38441] Change showing redeem button and restvalue.

# 4.5.0

- [#38552] New redemption condition: email address
- [#38471] redeemed & purchased vouchers are now displayed in the order detail in the administration

# 3.4.12

- [#38660] Fixed a bug in the administration where an error was thrown when the customer has no vouchers.

# 3.4.11

- [#38441] Change showing redeem button and restvalue.

# 3.4.10

- [#38292] Fixed problems with the export / import of the conditions of the vouchers

# 3.4.9

- [#38256] Adjusted meta-title for the overview of the vouchers

# 3.4.8

- [#38192] Fixed an error in voucher code generation

# 3.4.7

- [#37664] Error during recalculation of the order in the administration fixed

# 3.4.6

- [#37792] Fixed issue with condition "Maximal redemptions"
- [#37919] Calculation of the residual value in the administration improved

# 3.4.5

- [#37891] Adapted creation of random vouchers (for testing)

# 3.4.4

- [#37865] Fix problem on currency exchange at storefront

# 3.4.3

- [#37471] Fix calculation order of percentage and absolute vouchers in administration

# 3.4.2

- [#37510] Fixed error when considering deleted vouchers
- [#37499] Remove "curly braces"
- [#37495] Optimized snippets in checkout
- [#37483] Incorrect value display in administration for foreign currencies

# 4.4.1

- [#38485] Some redemption conditions were not selectable

# 3.4.1

- [#37365] change rule label from "Maximum amount" to "Maximum redemptions"
- [#37366] Improved display of general vouchers in the account
- [#37369] Fix problem with redemption condition check after cart change
- [#37372] Fix problem with maximum redemption value per customer

# 4.4.0

- [#36193] Send coupon activation mail via business event

# 3.4.0

- [#36544] New option for purchase vouchers: Validity time
- [#37094] Vouchers did not work after the order was edited in the administration

# 4.3.7

- [#38392] Fixed issues related to multiple order transactions when redeeming a voucher

# 4.3.6

- [#38292] Fixed problems with the export / import of the conditions of the vouchers

# 4.3.5

- [#38262] Error fixed when accessing the RequestStack
- [#38256] Adjusted meta-title for the overview of the vouchers

# 4.3.4

- [#38192] Fixed an error in voucher code generation
- [#38173] Fixed price display in storefront for purchase vouchers

# 4.3.3

- [#38132] Error when overwriting a subscriber fixed

# 4.3.2

- [#38114] Problem fixed during installation / update of the plugin

# 4.3.1

- [#37664] Error during recalculation of the order in the administration fixed

# 4.3.0

- [#36588] Improved voucher menu in the administration
- [#37697] New redemption condition: cart amount
- [#37792] Fixed issue with condition "Maximal redemptions"
- [#37919] Calculation of the residual value in the administration improved

# 3.3.0

- [#36620] Display of the customer's vouchers in the customer overview of the administration

# 3.2.2

- [#36878] Plugin setting "Default voucher code pattern" did not work
- [#36903] Removed invalid error message when redeeming a voucher in the administration order creation page

# 4.2.1

- [#37891] Adapted creation of random vouchers (for testing)

# 3.2.1

- [#36467] Add backend validation for purchase vouchers
- [#36632] Purchasable coupon conditions will now be deleted from the database if the coupon is deleted
- [#36595] Send header and footer with activation email

# 4.2.0

- [#35961] Added button to create transactions for vouchers in the administration

# 3.2.0
- [#36470] Added import/export profile

# 4.1.4

- [#37865] Fix problem on currency exchange at storefront

# 3.1.4
- [#36643] Change method call

# 4.1.3

- [#37786] Fixed problem with using the wrong class

# 3.1.3
- [#36633] Fixed incompatibility with LineItemCustomFieldRule

# 4.1.2

- [#37628] Restored compatibility when sending mail

# 3.1.2

- [#36593] Fix wrong output of an error message in SW 6.3.5.0

# 4.1.1

- [#37617] Fix problem with undefined method
- [#37471] Fix calculation order of percentage and absolute vouchers in administration

# 3.1.1
- [#36499] Sorting in transaction overview did not work
- [#36521] Adjustment of texts in the admin
- [#36538] Added custom date range rule
- [#36573] Orders could no longer be created in the administration

# 4.1.0

- [#37492] Orders/customers can now be opened directly in the transactions of a voucher
- [#37488] Removal of unused code
- [#37227] Vouchers can be placed in the shopping cart via a link with the code.
- [#36808] EasyCoupon vouchers can now run before or after Shopware promotions
- [#36623] API integration
- [#37510] Fixed error when considering deleted vouchers
- [#37499] Remove "curly braces"
- [#37495] Optimized snippets in checkout
- [#37483] Incorrect value display in administration for foreign currencies

# 3.1.0
- [#36034] Added current voucher status on voucher detail page in administration
- [#36295] Conditions for purchase vouchers
- [#36126] New condition for voucher: limitation to customer
- [#36085] New voucher property "Combine vouchers"
- [#36124] Added "copy code" button
- [#36466] Obsolete JavaScript optimized
- [#36125] Added value type column to voucher prodcuts listing
- [#36222] Removed redeem button in the account if remaining value of 0
- [#36214] Info if no voucher was found in the account
- [#36459] Display order number of voucher
- [#36404] Improved creation of mail templates
- [#36409] Check whether the plug-in is activated for the sale channel
- [#36172] Changed name of a condition
- [#36072] New plugin config: only show code after payment
- [#36406] Product price fields have been disabled for purchase vouchers without fixed value
- [#36073] Display remaining value of a voucher after order completion
- [#36405] Adjustment of English texts in the admin

# 3.0.2
- [#36335] Rename classes

# 3.0.1
- [#36302] Make faker only available in DEV mode
- [#36303] Adjust mail template installer

# 4.0.0

- [#37239] Adjustments for SW 6.4
- [#37367] New voucher rule "total redemption" to determine how often a voucher can be redeemed in total.

# 3.0.0
- [#35365] Initial release


=== Plugin Name ===
Contributors: levantoan
Tags: district shipping, shipping, tỉnh thành, quận huyện, tính phí ship cho quận huyện
Requires at least: 4.1
Requires PHP: 5.2.4
Tested up to: 5.0.3
Stable tag: 2.1.4

/*
* 1 số code hỗ trợ thêm
* Author Lê Văn Toản
* http://levantoan.com/plugin-tinh-phi-van-chuyen-cho-quan-huyen-trong-woocommerce/
*/
=========================================================
= Support hiển thị tên tỉnh thành; quận huyện; xã phường khi dùng plugin =
= Advanced Order Export For WooCommerce =
= https://wordpress.org/plugins/woo-order-export-lite/ =

add_filter('woe_get_order_value_billing_state', 'devvn_billing_state_format', 10, 3);
function devvn_billing_state_format($value, $order, $field){
    return devvn_vietnam_shipping()->get_name_city($value);
}

add_filter('woe_get_order_value_billing_city', 'devvn_billing_city_format', 10, 3);
function devvn_billing_city_format($value, $order, $field){
    return devvn_vietnam_shipping()->get_name_district($value);
}

add_filter('woe_get_order_value_billing_address_2', 'devvn_billing_address2_format', 10, 3);
function devvn_billing_address2_format($value, $order, $field){
    return devvn_vietnam_shipping()->get_name_village($value);
}

=========================================================

== Changelog ==

= 4.2.4 - 14.08.2019 =

* Fix: Sửa lỗi với phiên bản 3.7.0
* Update: Thêm các khu ở huyện Côn Đảo

= 4.2.3 - 30.05.2019 =

* Fix: Sửa lại chức năng lấy địa chỉ qua số điện thoại mua hàng trước đó khi khách chưa có tài khoản

= 4.2.2 - 27.05.2019 =

* Update: Sắp xếp quận huyện và xã phường theo A->Z

= 4.2.1 - 24.05.2019 =

* Add: Thêm tính năng "Lấy địa chỉ mua hàng trước" bằng "số điện thoại khách hàng"
* Fix: Sửa lỗi không load được quận huyện khi thêm đơn hàng mới trong admin

= 4.2.0 - 14.02.2019 =

* Fix: sửa lỗi thứ tự các field trong phần địa chỉ của khách hàng
* Add: Thêm chức năng sửa số điện thoại nhận hàng trong admin

= 4.1.9.1 - 07.11.2018 =

*Fix: Fix nhanh lỗi hiển thị địa chỉ ở bản update 4.1.9

= 4.1.9 - 06.11.2018 =

* Fix: Sửa một số lỗi js trong lúc cài đặt phí vận chuyển cho quận huyện
* Update: Cập nhật tương thích với Woocommerce 3.5.1

= 4.1.8 - 30.10.2018 =

* Update: Cập nhật tương thích với Woocommerce 3.5.0

= 4.1.7 - 27.06.2018 =

* Update: Sắp xếp tỉnh thành theo chữ cái A-Z. Chuyển Hà Nội và Hồ Chí Minh lên đầu tiên
* FIX: Hiển thị tên tỉnh thành trong thông tin địa chỉ đơn hàng

= 4.1.6 - 01.06.2018 =

* Thay đổi giá trị của tỉnh thành (Từ số -> chữ)
* Chuyển Hà Nội và Hồ Chí Minh lên đầu danh sách tỉnh thành
* Thay đổi link cài đặt thành Woocommerce -> Woo VN Checkout
* Chia địa chỉ thành 2 cột cho form checkout gọn gàng hơn
* Thêm placeholder cho số điện thoại và email

= 4.1.5 - 03.04.2018 =

* FIX: Sửa lỗi không hiển thị trường first_name khi kích hoạt hỗ trợ thanh toán qua Alepay

= 4.1.4 - 12.03.2018 =

* Update: Thêm js ở phần tính phí vận chuyển tại trang giỏ hàng để phù hợp với 1 số theme
* Update: Hiển thị tên của tỉnh/thành phố, quận huyện và xã phường thị trấn trong APP IOS của Woocommerce

= 4.1.3 - 08.03.2018 =

* Update: Với bản Woo 3.3.x đã sử dụng được tính năng tính phí vận chuyển theo quận/huyện tại trang giỏ hàng.

= 4.1.2 - 07.03.2018 =

* Fix: Sửa lỗi khi ẩn mục xã phường ở bản 4.1.1

= 4.1.1 - 06.03.2018 =

* Update: Tự động chuyển đổi gram (g) sang kilogam để tính phí vận chuyển
* Add: Thêm mục license key để update plugin tự động

= 4.1.0 - 27.02.2018 =

* Update: Support cổng thanh toán Alepay (Setting -> Cài đặt GHTK -> Kích hoạt Alepay)
* Update: 99% Tương thích với plugin "WooCommerce Checkout Field Editor (Manager) Pro"
* Update: Cho phép chỉnh sửa địa chỉ trong trang my account
* Update: Ghi nhớ địa chỉ của khách hàng đã checkout. Khách không cần nhập lại địa chỉ từ lần thứ 2 trở đi
* Fix: sửa 1 số lỗi với Flatsome theme
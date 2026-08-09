<?php
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['login_role']) || $_SESSION['login_role'] !== 9) {
    header('location: home');
    exit;
}
$cashier_id = intval($_SESSION['login_id']);
$cashier = $conn->query("SELECT u.*, b.branch_name, b.branch_code FROM users u LEFT JOIN branches b ON b.id = u.branch_id WHERE u.id = $cashier_id")->fetch_assoc();
$branch_id = intval($cashier['branch_id'] ?? 0);
$branch_name = htmlspecialchars($cashier['branch_name'] ?? '');
$branch_code = htmlspecialchars($cashier['branch_code'] ?? '');
$products = [];
if ($branch_id > 0) {
    $res = $conn->query("SELECT id, product_code, product_name, unit_price, quantity_on_hand, unit, image FROM products WHERE status=1 AND branch_id = $branch_id ORDER BY product_name ASC");
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<style>
    .sales-card {
        border-top: 3px solid #219688;
        border-radius: 4px;
        background: #fff;
    }

    .sales-heading {
        color: #219688;
    }

    .product-table tbody tr:hover {
        background: #f0faf9;
    }

    .cart-table tbody tr:hover {
        background: #fff4e5;
    }

    .badge-stock {
        font-size: 11px;
        font-weight: 700;
    }

    .sales-summary {
        background: #f8fafb;
        border-radius: 8px;
        padding: 20px;
    }

    .sales-summary h6 {
        font-weight: 700;
        margin-bottom: 15px;
    }

    .sales-summary .summary-value {
        font-size: 24px;
        font-weight: 800;
        color: #219688;
    }

    .sales-summary .summary-label {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .form-control-sm {
        padding: .4rem .6rem;
    }

    .cart-qty-control {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    .cart-qty-control .btn {
        width: 28px;
        height: 28px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .cart-qty-control .cart-item-qty {
        width: 54px;
        text-align: center;
    }

    .cart-item-price {
        width: 92px;
        text-align: right;
    }

    .cart-qty-control input[type=number]::-webkit-inner-spin-button,
    .cart-qty-control input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .cart-qty-control input[type=number] {
        appearance: textfield;
        -moz-appearance: textfield;
    }

    .product-thumb,
    .cart-item-thumb {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
    }

    .cart-item-thumb {
        width: 34px;
        height: 34px;
    }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <div>
                            <h4 class="mb-sm-0 sales-heading"><i class="ri-shopping-cart-2-line me-2"></i>Sales Transaction</h4>
                            <p class="text-muted mb-0 small mt-1"><i class="ri-information-line"></i> Process cashier sales and save receipts to POS.</p>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Cashier</a></li>
                                <li class="breadcrumb-item active">Sales Transaction</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($branch_id <= 0): ?>
                <div class="alert alert-warning">
                    <h5 class="alert-heading">Branch not assigned</h5>
                    <p>Your cashier account has no branch assigned. Please ask the administrator to assign a branch in your user account.</p>
                </div>
            <?php else: ?>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card sales-card p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted">Branch</div>
                                    <div class="h5 mb-0"><?= $branch_code ?> - <?= $branch_name ?></div>
                                </div>
                                <div>
                                    <span class="badge bg-success badge-stock">Cashier</span>
                                    <div class="text-end text-muted" style="font-size:13px;"><?= htmlspecialchars($_SESSION['login_name']) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-7">
                        <div class="card sales-card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Available Products</h5>
                                <span class="badge bg-soft-primary text-primary">Branch inventory</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover table-bordered product-table mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:10%;">Code</th>
                                                <th>Product</th>
                                                <th style="width:12%;">Price</th>
                                                <th style="width:12%;">Stock</th>
                                                <th style="width:12%;">Qty</th>
                                                <th style="width:12%;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($products) === 0): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">No active products found for this branch.</td>
                                                </tr>
                                                <?php else: foreach ($products as $p): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($p['product_code']) ?></td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <img src="<?= !empty($p['image']) ? 'uploads/products/' . htmlspecialchars($p['image'], ENT_QUOTES) : 'assets/images/no-image.svg' ?>"
                                                                     alt="<?= htmlspecialchars($p['product_name'], ENT_QUOTES) ?>"
                                                                     class="product-thumb">
                                                                <div class="fw-semibold"><?= htmlspecialchars($p['product_name']) ?></div>
                                                            </div>
                                                        </td>
                                                        <td class="text-end">&#8369; <?= number_format($p['unit_price'], 2) ?></td>
                                                        <td class="text-center">
                                                            <span class="badge bg-<?= $p['quantity_on_hand'] > 5 ? 'success' : ($p['quantity_on_hand'] > 0 ? 'warning' : 'danger') ?> badge-stock">
                                                                <?= intval($p['quantity_on_hand']) ?> <?= htmlspecialchars($p['unit']) ?></span>
                                                        </td>
                                                        <td>
                                                            <input type="number" min="1" max="<?= intval($p['quantity_on_hand']) ?>" value="1" class="form-control form-control-sm product-qty" data-product-id="<?= intval($p['id']) ?>">
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-outline-primary btn-add-product"
                                                                data-id="<?= intval($p['id']) ?>"
                                                                data-name="<?= htmlspecialchars($p['product_name'], ENT_QUOTES) ?>"
                                                                data-price="<?= floatval($p['unit_price']) ?>"
                                                                data-stock="<?= intval($p['quantity_on_hand']) ?>"
                                                                data-unit="<?= htmlspecialchars($p['unit'], ENT_QUOTES) ?>"
                                                                data-image="<?= !empty($p['image']) ? 'uploads/products/' . htmlspecialchars($p['image'], ENT_QUOTES) : 'assets/images/no-image.svg' ?>">
                                                                <i class="ri-add-line"></i> Add
                                                            </button>
                                                        </td>
                                                    </tr>
                                            <?php endforeach;
                                            endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="card sales-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Current Cart</h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm cart-table mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Item</th>
                                                <th class="text-end">Price</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-end">Total</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="cart-body">
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No items in cart.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="sales-summary">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="summary-label">Subtotal</span>
                                        <span class="summary-value" id="summary-subtotal">₱ 0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="summary-label">Discount</span>
                                        <span class="summary-value text-danger" id="summary-discount">₱ 0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="summary-label">Total</span>
                                        <span class="summary-value" id="summary-total">₱ 0.00</span>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small mb-1">Discount</label>
                                        <input type="number" id="input-discount" class="form-control form-control-sm" min="0" step="0.01" value="0.00">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small mb-1">Payment</label>
                                        <input type="number" id="input-payment" class="form-control form-control-sm" min="0" step="0.01" value="0.00">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small mb-1">Change</label>
                                        <input type="text" id="input-change" class="form-control form-control-sm" readonly value="₱ 0.00">
                                    </div>
                                    <button type="button" id="btn-save-sale" class="btn btn-primary w-100" disabled>Save Transaction</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <!-- Confirm Save Modal -->
            <div class="modal fade" id="confirmSaveModal" tabindex="-1" aria-labelledby="confirmSaveModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmSaveModalLabel">Confirm Save Transaction</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="confirm-save-summary">
                                <div class="mb-2"><strong>Subtotal:</strong> <span id="confirm-subtotal">₱ 0.00</span></div>
                                <div class="mb-2"><strong>Discount:</strong> <span id="confirm-discount">₱ 0.00</span></div>
                                <div class="mb-2"><strong>Total:</strong> <span id="confirm-total">₱ 0.00</span></div>
                                <div class="mb-2"><strong>Payment:</strong> <span id="confirm-payment">₱ 0.00</span></div>
                                <div class="mb-2"><strong>Change:</strong> <span id="confirm-change">₱ 0.00</span></div>
                                <hr />
                                <div class="mb-2"><strong>Items</strong></div>
                                <div id="confirm-items-list" style="max-height:200px;overflow:auto;">
                                </div>
                            </div>
                            <div id="confirm-save-error" class="alert alert-danger d-none" role="alert"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="confirm-save-yes" class="btn btn-primary">Confirm Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

            <!-- Save Result Modal -->
            <div class="modal fade" id="saveResultModal" tabindex="-1" aria-labelledby="saveResultModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center py-4">
                            <div class="mb-3">
                                <i class="ri-checkbox-circle-line" style="font-size:36px;color:#28a745"></i>
                            </div>
                            <h5 id="save-result-title" class="mb-2">Sale saved successfully</h5>
                            <div id="save-result-message" class="mb-2">&nbsp;</div>
                            <div id="save-result-invoice" class="fw-bold mb-3"></div>
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var cart = [];
        var branchId = <?= $branch_id ?>;
        var cashierId = <?= $cashier_id ?>;

        function peso(value) {
            return '₱ ' + (parseFloat(value || 0)).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function getCartItemIndex(productId) {
            return cart.findIndex(function(item) {
                return parseInt(item.id, 10) === parseInt(productId, 10);
            });
        }

        function updateCart() {
            var subtotal = 0;
            var body = '';

            cart.forEach(function(item, index) {
                var line = parseFloat(item.price) * parseInt(item.qty, 10);
                subtotal += line;
                var imageSrc = item.image ? item.image : 'assets/images/no-image.svg';
                if (item.qty == 1) {
                    body += '<tr>' +
                        '<td><div class="d-flex align-items-center gap-2"><img src="' + imageSrc + '" alt="' + (item.name || 'Product') + '" class="cart-item-thumb"><div>' + item.name + '</div></div></td>' +
                        '<td class="text-end"><input type="number" min="0" step="0.01" class="form-control form-control-sm cart-item-price" data-index="' + index + '" value="' + parseFloat(item.price).toFixed(2) + '" aria-label="Price for ' + (item.name || 'Product') + '"></td>' +
                        '<td class="text-center">' +
                        '<div class="cart-qty-control" aria-label="Quantity controls">' +
                        '<button type="button" class="btn btn-sm btn-danger btn-remove-item" data-index="' + index + '" aria-label="Remove item"><i class="ri-delete-bin-line"></i></button>' +
                        '<input type="number" min="1" max="' + item.stock + '" class="form-control form-control-sm cart-item-qty" data-index="' + index + '" value="' + parseInt(item.qty, 10) + '">' +
                        '<button type="button" class="btn btn-sm btn-outline-secondary btn-cart-qty-plus" data-index="' + index + '" aria-label="Increase quantity"><i class="ri-add-line"></i></button>' +
                        '</div>' +
                        '</td>' +
                        '<td class="text-end">' + peso(line) + '</td>' +
                        '<td class="text-center"><button type="button" class="btn btn-sm btn-danger btn-remove-item" data-index="' + index + '" aria-label="Remove item"><i class="ri-delete-bin-line"></i></button></td>' +
                        '</tr>';
                } else {
                    body += '<tr>' +
                        '<td><div class="d-flex align-items-center gap-2"><img src="' + imageSrc + '" alt="' + (item.name || 'Product') + '" class="cart-item-thumb"><div>' + item.name + '</div></div></td>' +
                        '<td class="text-end"><input type="number" min="0" step="0.01" class="form-control form-control-sm cart-item-price" data-index="' + index + '" value="' + parseFloat(item.price).toFixed(2) + '" aria-label="Price for ' + (item.name || 'Product') + '"></td>' +
                        '<td class="text-center">' +
                        '<div class="cart-qty-control" aria-label="Quantity controls">' +
                        '<button type="button" class="btn btn-sm btn-outline-secondary btn-cart-qty-minus" data-index="' + index + '" aria-label="Decrease quantity"><i class="ri-subtract-line"></i></button>' +
                        '<input type="number" min="1" max="' + item.stock + '" class="form-control form-control-sm cart-item-qty" data-index="' + index + '" value="' + parseInt(item.qty, 10) + '">' +
                        '<button type="button" class="btn btn-sm btn-outline-secondary btn-cart-qty-plus" data-index="' + index + '" aria-label="Increase quantity"><i class="ri-add-line"></i></button>' +
                        '</div>' +
                        '</td>' +
                        '<td class="text-end">' + peso(line) + '</td>' +
                        '<td class="text-center"><button type="button" class="btn btn-sm btn-danger btn-remove-item" data-index="' + index + '" aria-label="Remove item"><i class="ri-delete-bin-line"></i></button></td>' +
                        '</tr>';
                }
            });

            if (cart.length === 0) {
                body = '<tr><td colspan="5" class="text-center text-muted py-4">No items in cart.</td></tr>';
            }

            $('#cart-body').html(body);
            $('#summary-subtotal').text(peso(subtotal));

            var discount = parseFloat($('#input-discount').val()) || 0;
            if (discount < 0) discount = 0;
            if (discount > subtotal) discount = subtotal;
            $('#summary-discount').text(peso(discount));

            var total = subtotal - discount;
            $('#summary-total').text(peso(total));

            var payment = parseFloat($('#input-payment').val()) || 0;
            var change = payment > total ? payment - total : 0;
            $('#input-change').val(peso(change));

            $('#btn-save-sale').prop('disabled', cart.length === 0 || total <= 0);
        }

        function addProductToCart(product) {
            if (product.qty <= 0) return;
            var index = getCartItemIndex(product.id);
            if (index >= 0) {
                cart[index].qty = parseInt(cart[index].qty, 10) + parseInt(product.qty, 10);
                if (cart[index].qty > product.stock) {
                    cart[index].qty = product.stock;
                }
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: parseFloat(product.price),
                    qty: parseInt(product.qty, 10),
                    stock: parseInt(product.stock, 10),
                    image: product.image || 'assets/images/no-image.svg'
                });
            }
            updateCart();
        }

        function setCartItemQty(index, qty) {
            if (!isNaN(index) && index >= 0 && index < cart.length) {
                qty = parseInt(qty, 10) || 1;
                if (qty < 1) qty = 1;
                if (qty > cart[index].stock) qty = cart[index].stock;
                cart[index].qty = qty;
                updateCart();
            }
        }

        function setCartItemPrice(index, price) {
            if (!isNaN(index) && index >= 0 && index < cart.length) {
                var parsedPrice = parseFloat(price);
                if (!isFinite(parsedPrice) || parsedPrice < 0) {
                    updateCart();
                    return;
                }
                cart[index].price = parsedPrice;
                updateCart();
            }
        }

        $(document).on('click', '.btn-add-product', function() {
            var btn = $(this);
            var id = parseInt(btn.data('id'), 10);
            var name = btn.data('name');
            var price = parseFloat(btn.data('price')) || 0;
            var stock = parseInt(btn.data('stock'), 10) || 0;
            var image = btn.data('image') || 'assets/images/no-image.svg';
            var qtyInput = $('.product-qty[data-product-id="' + id + '"]');
            var qty = parseInt(qtyInput.val(), 10) || 1;
            if (qty < 1) qty = 1;
            if (qty > stock) qty = stock;
            addProductToCart({
                id: id,
                name: name,
                price: price,
                qty: qty,
                stock: stock,
                image: image
            });
        });

        $(document).on('click', '.btn-remove-item', function() {
            var index = parseInt($(this).data('index'), 10);
            if (!isNaN(index) && index >= 0 && index < cart.length) {
                cart.splice(index, 1);
                updateCart();
            }
        });

        $(document).on('change', '.cart-item-qty', function() {
            var index = parseInt($(this).data('index'), 10);
            setCartItemQty(index, $(this).val());
        });

        $(document).on('change', '.cart-item-price', function() {
            var index = parseInt($(this).data('index'), 10);
            setCartItemPrice(index, $(this).val());
        });

        $(document).on('click', '.btn-cart-qty-minus', function() {
            var index = parseInt($(this).data('index'), 10);
            if (!isNaN(index) && index >= 0 && index < cart.length) {
                setCartItemQty(index, parseInt(cart[index].qty, 10) - 1);
            }
        });

        $(document).on('click', '.btn-cart-qty-plus', function() {
            var index = parseInt($(this).data('index'), 10);
            if (!isNaN(index) && index >= 0 && index < cart.length) {
                setCartItemQty(index, parseInt(cart[index].qty, 10) + 1);
            }
        });

        $('#input-discount, #input-payment').on('input', function() {
            updateCart();
        });

        function saveTransaction() {
            var subtotal = 0;
            cart.forEach(function(item) {
                subtotal += parseFloat(item.price) * parseInt(item.qty, 10);
            });
            if (subtotal <= 0) {
                alert('Add items to the cart before saving.');
                return;
            }
            var discount = parseFloat($('#input-discount').val()) || 0;
            if (discount < 0) discount = 0;
            if (discount > subtotal) discount = subtotal;
            var payment = parseFloat($('#input-payment').val()) || 0;
            var total = subtotal - discount;
            if (payment < total) {
                alert('Payment must be greater than or equal to total.');
                return;
            }
            var payload = {
                branch_id: branchId,
                cashier_id: cashierId,
                discount: discount,
                payment: payment,
                items: cart.map(function(item) {
                    return {
                        product_id: item.id,
                        product_name: item.name,
                        price: item.price,
                        qty: item.qty
                    };
                })
            };
            $('#btn-save-sale').prop('disabled', true).text('Saving...');
            $.ajax({
                url: 'ajax.php?action=mobile-pos-save-sale',
                method: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json',
                dataType: 'json'
            }).done(function(res) {
                if (res && res.result) {
                    // show result in modal
                    $('#save-result-message').text('Sale saved successfully. Invoice: ' + (res.invoice_no || 'N/A'));
                    // update product stock badges on page if server returned updated stocks
                    if (res.updated_stocks) {
                        Object.keys(res.updated_stocks).forEach(function(pid) {
                            var newQty = res.updated_stocks[pid];
                            // find add button for product
                            var btn = $('.btn-add-product[data-id="' + pid + '"]');
                            if (btn.length) {
                                var unit = btn.data('unit') || '';
                                // update badge in the same row
                                var badge = btn.closest('tr').find('.badge-stock');
                                if (badge.length) {
                                    var qtyInt = parseFloat(newQty) || 0;
                                    var cls = qtyInt > 5 ? 'success' : (qtyInt > 0 ? 'warning' : 'danger');
                                    badge.removeClass('bg-success bg-warning bg-danger').addClass('bg-' + cls);
                                    badge.text(qtyInt + ' ' + unit);
                                }
                                // update button data-stock
                                btn.data('stock', newQty);
                                btn.attr('data-stock', newQty);
                                // update qty input max
                                var qtyInput = $('.product-qty[data-product-id="' + pid + '"]');
                                if (qtyInput.length) qtyInput.attr('max', newQty);
                            }
                        });
                    }
                    // ensure no stray modal backdrops remain then show result modal
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('#save-result-invoice').text('Invoice: ' + (res.invoice_no || 'N/A'));
                    var resultModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('saveResultModal'));
                    resultModal.show();
                    // reset cart and inputs
                    cart = [];
                    $('#input-discount').val('0.00');
                    $('#input-payment').val('0.00');
                    $('#input-change').val('₱ 0.00');
                    updateCart();
                } else {
                    $('#save-result-message').text((res && res.message) ? res.message : 'Unable to save sale.');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    var resultModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('saveResultModal'));
                    resultModal.show();
                }
            }).fail(function() {
                $('#save-result-message').text('Unable to save sale. Please try again.');
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
                var resultModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('saveResultModal'));
                resultModal.show();
            }).always(function() {
                updateCart();
                $('#btn-save-sale').text('Save Transaction');
            });
        }

        function prepareConfirmModal() {
            var subtotal = 0;
            cart.forEach(function(item) {
                subtotal += parseFloat(item.price) * parseInt(item.qty, 10);
            });
            if (subtotal <= 0) {
                // show quick alert if no items
                alert('Add items to the cart before saving.');
                return;
            }
            var discount = parseFloat($('#input-discount').val()) || 0;
            if (discount < 0) discount = 0;
            if (discount > subtotal) discount = subtotal;
            var payment = parseFloat($('#input-payment').val()) || 0;
            var total = subtotal - discount;
            var change = payment > total ? payment - total : 0;

            if (payment < total) {
                // show error in modal-like alert for clarity
                alert('Payment must be greater than or equal to total.');
                return;
            }

            // populate modal
            $('#confirm-subtotal').text(peso(subtotal));
            $('#confirm-discount').text(peso(discount));
            $('#confirm-total').text(peso(total));
            $('#confirm-payment').text(peso(payment));
            $('#confirm-change').text(peso(change));

            var itemsHtml = '<ul class="list-unstyled mb-0">';
            cart.forEach(function(item) {
                itemsHtml += '<li>' + (item.name || 'Item') + ' &times; ' + item.qty + ' — ' + peso(parseFloat(item.price) * parseInt(item.qty, 10)) + '</li>';
            });
            itemsHtml += '</ul>';
            $('#confirm-items-list').html(itemsHtml);

            $('#confirm-save-error').addClass('d-none').text('');
            // show modal (Bootstrap 5)
            var modalEl = document.getElementById('confirmSaveModal');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl, {keyboard: false});
            modal.show();
        }

        $('#btn-save-sale').on('click', function() {
            prepareConfirmModal();
        });

        // when user confirms in modal
        $('#confirm-save-yes').on('click', function() {
            // disable confirm button to prevent double click
            $(this).prop('disabled', true).text('Saving...');
            // hide modal
            var modalEl = document.getElementById('confirmSaveModal');
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            // call save
            saveTransaction();
            // re-enable after some time or after save finishes (saveTransaction will update UI)
            $(this).prop('disabled', false).text('Confirm Save');
        });

        updateCart();
    });
</script>
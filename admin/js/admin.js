jQuery(document).ready(function ($) {
    'use strict';
    
    console.log('BWD Admin JS loaded successfully');
    console.log('bwd_ajax object:', typeof bwd_ajax !== 'undefined' ? bwd_ajax : 'NOT DEFINED');
    
    // Check if tab elements exist
    console.log('Tab buttons found:', $('.wbdn-tab-nav-btn').length);
    console.log('Tab content found:', $('.wbdn-tab-content').length);

    // Batch processing
    $('#bwd-start-batch').on('click', function () {
        if (!confirm(bwd_ajax.strings.confirm_batch)) {
            return;
        }

        var $button = $(this);
        var $status = $('#bwd-batch-status');

        $button.prop('disabled', true).text(bwd_ajax.strings.processing);
        $status.show().find('.bwd-status-message').text('در حال شروع پردازش...');

        processBatch();
    });

    // Reset progress
    $('#bwd-reset-progress').on('click', function () {
        if (confirm('آیا مطمئن هستید که می‌خواهید پیشرفت را بازنشانی کنید؟')) {
            $.ajax({
                url: bwd_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'bwd_reset_progress',
                    nonce: bwd_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('خطا در بازنشانی پیشرفت');
                    }
                },
                error: function () {
                    alert('خطا در ارتباط با سرور');
                }
            });
        }
    });

    // Count products
    $('#bwd-count-products').on('click', function () {
        var $button = $(this);

        $button.prop('disabled', true).text('در حال شمارش...');

        $.ajax({
            url: bwd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bwd_count_products',
                nonce: bwd_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('خطا در شمارش محصولات');
                }
            },
            error: function () {
                alert('خطا در ارتباط با سرور');
            },
            complete: function () {
                $button.prop('disabled', false).text('شمارش محصولات');
            }
        });
    });

    // Refresh stats
    $('#bwd-refresh-stats').on('click', function () {
        var $button = $(this);

        $button.prop('disabled', true).text('در حال بروزرسانی...');

        $.ajax({
            url: bwd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bwd_count_products',
                nonce: bwd_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('خطا در بروزرسانی آمار');
                }
            },
            error: function () {
                alert('خطا در ارتباط با سرور');
            },
            complete: function () {
                $button.prop('disabled', false).text('بروزرسانی آمار');
            }
        });
    });

    // Cleanup stats
    $('#bwd-cleanup-stats').on('click', function () {
        var $button = $(this);

        if (confirm('آیا مطمئن هستید که می‌خواهید داده‌های اضافی را پاک کنید و آمار را محاسبه مجدد کنید؟')) {
            $button.prop('disabled', true).text('در حال پاکسازی...');

            $.ajax({
                url: bwd_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'bwd_cleanup_stats',
                    nonce: bwd_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        alert('پاکسازی با موفقیت انجام شد. آمار جدید:\n' +
                            'کل محصولات: ' + response.data.total_products + '\n' +
                            'نرمالایز شده: ' + response.data.normalized_products + '\n' +
                            'نیاز به نرمالایز: ' + response.data.products_needing_normalization + '\n' +
                            'درصد پیشرفت: ' + response.data.percentage + '%');
                        location.reload();
                    } else {
                        alert('خطا در پاکسازی');
                    }
                },
                error: function () {
                    alert('خطا در ارتباط با سرور');
                },
                complete: function () {
                    $button.prop('disabled', false).text('پاکسازی و محاسبه مجدد');
                }
            });
        }
    });

    // Test settings
    $('#bwd-test-settings').on('click', function () {
        var $button = $(this);

        $button.prop('disabled', true).text('در حال تست...');

        $.ajax({
            url: bwd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bwd_test_settings',
                nonce: bwd_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    var settings = response.data;
                    var message = 'تنظیمات فعلی:\n';
                    message += 'اندازه دسته: ' + settings.batch_size + '\n';
                    message += 'حذف کلمات اضافی: ' + (settings.remove_stopwords ? 'فعال' : 'غیرفعال') + '\n';
                    message += 'پردازش مجدد محصولات موجود: ' + (settings.reprocess_existing ? 'فعال' : 'غیرفعال') + '\n';
                    message += 'نرمالایز خودکار: ' + (settings.auto_normalize ? 'فعال' : 'غیرفعال');

                    alert(message);
                } else {
                    alert('خطا در دریافت تنظیمات');
                }
            },
            error: function () {
                alert('خطا در ارتباط با سرور');
            },
            complete: function () {
                $button.prop('disabled', false).text('تست تنظیمات فعلی');
            }
        });
    });

    // Refresh logs
    $('#bwd-refresh-logs').on('click', function () {
        var $button = $(this);

        $button.prop('disabled', true).text('در حال بروزرسانی...');

        $.ajax({
            url: bwd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bwd_refresh_logs',
                nonce: bwd_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('خطا در بروزرسانی لاگ‌ها');
                }
            },
            error: function () {
                alert('خطا در ارتباط با سرور');
            },
            complete: function () {
                $button.prop('disabled', false).text('بروزرسانی لاگ‌ها');
            }
        });
    });

    // Clear logs
    $('#bwd-clear-logs').on('click', function () {
        if (confirm('آیا مطمئن هستید که می‌خواهید تمام لاگ‌ها را پاک کنید؟')) {
            var $button = $(this);

            $button.prop('disabled', true).text('در حال پاک کردن...');

            $.ajax({
                url: bwd_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'bwd_clear_logs',
                    nonce: bwd_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('خطا در پاک کردن لاگ‌ها');
                    }
                },
                error: function () {
                    alert('خطا در ارتباط با سرور');
                },
                complete: function () {
                    $button.prop('disabled', false).text('پاک کردن لاگ‌ها');
                }
            });
        }
    });

    // Reprocess product
    $(document).on('click', '.bwd-reprocess-product', function () {
        var $button = $(this);
        var productId = $button.data('post-id');

        if (!productId) {
            alert('شناسه محصول نامعتبر است');
            return;
        }

        if (confirm('آیا مطمئن هستید که می‌خواهید این محصول را دوباره نرمالایز کنید؟')) {
            $button.prop('disabled', true).text('در حال پردازش...');

            $.ajax({
                url: bwd_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'bwd_reprocess_product',
                    product_id: productId,
                    nonce: bwd_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        alert('محصول با موفقیت دوباره نرمالایز شد');
                        location.reload();
                    } else {
                        alert('خطا: ' + (response.data || 'خطای نامشخص'));
                    }
                },
                error: function () {
                    alert('خطا در ارتباط با سرور');
                },
                complete: function () {
                    $button.prop('disabled', false).text('پردازش مجدد');
                }
            });
        }
    });

    // Fix activation status
    $('#bwd-fix-activation').on('click', function () {
        var $button = $(this);

        if (confirm('آیا مطمئن هستید که می‌خواهید وضعیت فعال‌سازی پلاگین را تنظیم مجدد کنید؟')) {
            $button.prop('disabled', true).text('در حال تنظیم...');

            $.ajax({
                url: bwd_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'bwd_fix_activation',
                    nonce: bwd_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        alert('وضعیت فعال‌سازی با موفقیت تنظیم شد');
                        location.reload();
                    } else {
                        alert('خطا: ' + (response.data || 'خطای نامشخص'));
                    }
                },
                error: function () {
                    alert('خطا در ارتباط با سرور');
                },
                complete: function () {
                    $button.prop('disabled', false).text('تنظیم مجدد وضعیت');
                }
            });
        }
    });



    // Manual normalization
    $('#bwd-normalize-single').on('click', function () {
        var productId = $('#bwd-product-id').val();

        if (!productId) {
            alert('لطفاً شناسه محصول را وارد کنید');
            return;
        }

        var $button = $(this);
        var $status = $('#bwd-manual-status');

        $button.prop('disabled', true).text('در حال پردازش...');
        $status.show().find('.bwd-status-message').text('در حال نرمالایز کردن محصول...');

        $.ajax({
            url: bwd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bwd_normalize_single',
                product_id: productId,
                nonce: bwd_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    $status.find('.bwd-status-message').text('محصول با موفقیت نرمالایز شد');
                    $('#bwd-product-id').val('');
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                } else {
                    $status.find('.bwd-status-message').text('خطا: ' + (response.data || 'خطای نامشخص'));
                }
            },
            error: function () {
                $status.find('.bwd-status-message').text('خطا در ارتباط با سرور');
            },
            complete: function () {
                $button.prop('disabled', false).text('نرمالایز کردن');
            }
        });
    });

    // Test normalization
    $('#bwd-test-normalize').on('click', function () {
        var testText = $('#bwd-test-text').val();

        if (!testText) {
            alert('لطفاً متنی برای تست وارد کنید');
            return;
        }

        var $button = $(this);
        var $result = $('#bwd-test-result');

        $button.prop('disabled', true).text('در حال تست...');

        $.ajax({
            url: bwd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bwd_test_normalize',
                text: testText,
                nonce: bwd_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    $result.show().find('.bwd-result-text').text(response.data.normalized_text);
                } else {
                    $result.show().find('.bwd-result-text').text('خطا در نرمالایز کردن متن');
                }
            },
            error: function () {
                $result.show().find('.bwd-result-text').text('خطا در ارتباط با سرور');
            },
            complete: function () {
                $button.prop('disabled', false).text('تست نرمالایز کردن');
            }
        });
    });

    // Settings form
    $('#bwd-settings-form').on('submit', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $submitButton = $form.find('button[type="submit"]');
        var $status = $('#bwd-settings-status');

        $submitButton.prop('disabled', true).text('در حال ذخیره...');
        $status.hide();

        var formData = {
            action: 'bwd_save_settings',
            nonce: bwd_ajax.nonce,
            batch_size: $('#bwd-batch-size').val(),
            remove_stopwords: $('#bwd-remove-stopwords').is(':checked') ? 'true' : 'false',
            reprocess_existing: $('#bwd-reprocess-existing').is(':checked') ? 'true' : 'false',
            auto_normalize: $('#bwd-auto-normalize').is(':checked') ? 'true' : 'false',

        };

        console.log('Sending settings:', formData); // Debug log

        $.ajax({
            url: bwd_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function (response) {
                console.log('Settings response:', response); // Debug log

                if (response.success) {
                    $status.removeClass('error').addClass('success')
                        .find('.bwd-status-message').text(response.data.message || response.data);
                    $status.show();

                    // Show success message for 5 seconds
                    setTimeout(function () {
                        $status.fadeOut();
                    }, 5000);

                    // Update form values to match saved values
                    if (response.data.saved_values) {
                        $('#bwd-batch-size').val(response.data.saved_values.batch_size);
                        $('#bwd-remove-stopwords').prop('checked', response.data.saved_values.remove_stopwords);
                        $('#bwd-reprocess-existing').prop('checked', response.data.saved_values.reprocess_existing);
                        $('#bwd-auto-normalize').prop('checked', response.data.saved_values.auto_normalize);
                    }
                } else {
                    $status.removeClass('success').addClass('error')
                        .find('.bwd-status-message').text('خطا: ' + (response.data || 'خطای نامشخص'));
                    $status.show();
                }
            },
            error: function () {
                $status.removeClass('success').addClass('error')
                    .find('.bwd-status-message').text('خطا در ارتباط با سرور');
                $status.show();
            },
            complete: function () {
                $submitButton.prop('disabled', false).text('ذخیره تنظیمات');
            }
        });
    });

    // Batch processing function
    function processBatch() {
        $.ajax({
            url: bwd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bwd_batch_update',
                nonce: bwd_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    updateProgress();

                    if (response.data.completed) {
                        $('#bwd-batch-status').find('.bwd-status-message').text(response.data.message);
                        $('#bwd-start-batch').prop('disabled', true).text('تکمیل شده');
                        return;
                    }

                    // Continue processing
                    setTimeout(processBatch, 1000);
                } else {
                    $('#bwd-batch-status').find('.bwd-status-message').text('خطا: ' + (response.data || 'خطای نامشخص'));
                    $('#bwd-start-batch').prop('disabled', false).text('شروع پردازش دسته‌ای');
                }
            },
            error: function () {
                $('#bwd-batch-status').find('.bwd-status-message').text('خطا در ارتباط با سرور');
                $('#bwd-start-batch').prop('disabled', false).text('شروع پردازش دسته‌ای');
            }
        });
    }

    // Update progress function
    function updateProgress() {
        $.ajax({
            url: bwd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bwd_get_progress',
                nonce: bwd_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    var data = response.data;

                    // Update statistics
                    $('.bwd-stat-item:nth-child(1) .bwd-stat-number').text(data.total.toLocaleString());
                    $('.bwd-stat-item:nth-child(2) .bwd-stat-number').text(data.processed.toLocaleString());
                    $('.bwd-stat-item:nth-child(3) .bwd-stat-number').text(data.percentage + '%');

                    // Update progress bar
                    $('.bwd-progress-fill').css('width', data.percentage + '%');
                    $('.bwd-progress-text').text(data.percentage + '% تکمیل شده');

                    // Update status message
                    if (!data.completed) {
                        $('#bwd-batch-status').find('.bwd-status-message').text(
                            'در حال پردازش... ' + data.processed + ' از ' + data.total + ' محصول'
                        );
                    }
                }
            }
        });
    }

    // Auto-refresh progress every 5 seconds during processing
    setInterval(function () {
        if ($('#bwd-start-batch').prop('disabled')) {
            updateProgress();
        }
    }, 5000);

    // Product meta box normalization
    $('.bwd-normalize-now').on('click', function () {
        var postId = $(this).data('post-id');
        var $button = $(this);

        $button.prop('disabled', true).text('در حال نرمالایز...');

        $.ajax({
            url: bwd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bwd_normalize_single',
                product_id: postId,
                nonce: bwd_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    $button.text('نرمالایز شد').addClass('button-primary');
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                } else {
                    $button.text('خطا').addClass('button-secondary');
                }
            },
            error: function () {
                $button.text('خطا').addClass('button-secondary');
            }
        });
    });

    // Keyboard shortcuts
    $(document).keydown(function (e) {
        // Ctrl/Cmd + Enter to start batch processing
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 13) {
            if (!$('#bwd-start-batch').prop('disabled')) {
                $('#bwd-start-batch').click();
            }
        }

        // Ctrl/Cmd + Shift + R to reset progress (to avoid conflict with page reload)
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.keyCode === 82) {
            $('#bwd-reset-progress').click();
        }
    });

    // Tooltips - disabled due to jQuery version compatibility
    // $('[title]').tooltip();

    // Responsive design
    function adjustLayout() {
        if ($(window).width() < 768) {
            $('.bwd-stats-grid').css('grid-template-columns', '1fr');
        } else {
            $('.bwd-stats-grid').css('grid-template-columns', 'repeat(auto-fit, minmax(150px, 1fr))');
        }
    }

    $(window).resize(adjustLayout);
    adjustLayout();

    // Tab functionality
    console.log('Setting up tab functionality...');
    
    $('.wbdn-tab-nav-btn').on('click', function (e) {
        e.preventDefault(); // Prevent default link behavior
        
        var tabId = $(this).data('tab');
        console.log('Tab clicked:', tabId); // Debug log

        // Remove active class from all tabs and content
        $('.wbdn-tab-nav-btn').removeClass('active');
        $('.wbdn-tab-content').removeClass('active');

        // Add active class to clicked tab and corresponding content
        $(this).addClass('active');
        $('#wbdn-tab-' + tabId).addClass('active');
        
        console.log('Tab switched to:', tabId); // Debug log
    });
    
    // Test if tab buttons are clickable
    $('.wbdn-tab-nav-btn').each(function() {
        console.log('Tab button found:', $(this).text(), 'data-tab:', $(this).data('tab'));
    });

    // Copy button functionality
    $('.bwd-copy-btn').on('click', function () {
        var targetId = $(this).data('copy');
        var codeElement = document.getElementById(targetId);

        if (codeElement) {
            // Create a temporary textarea to copy the text
            var textarea = document.createElement('textarea');
            textarea.value = codeElement.textContent;
            document.body.appendChild(textarea);
            textarea.select();

            try {
                // Copy the text to clipboard
                var successful = document.execCommand('copy');
                if (successful) {
                    // Show success feedback
                    var originalText = $(this).text();
                    $(this).text('کپی شد!').css('background', '#28a745');

                    // Reset button after 2 seconds
                    setTimeout(function () {
                        $(this).text(originalText).css('background', '#007bff');
                    }.bind(this), 2000);
                } else {
                    // Fallback: show alert
                    alert('کپی کردن با مشکل مواجه شد. لطفاً دستی کپی کنید.');
                }
            } catch (err) {
                // Fallback: show alert
                alert('کپی کردن پشتیبانی نمی‌شود. لطفاً دستی کپی کنید.');
            }

            // Remove temporary textarea
            document.body.removeChild(textarea);
        }
    });
});

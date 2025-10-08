/**
 * AJAX Form Handler Component for Admin
 * Handles form submissions via AJAX and displays toast notifications
 */
$(document).ready(function() {
    // AJAX Form Handler Component
    const AjaxFormHandler = {
        // Initialize the component
        init: function(options = {}) {
            this.table = options.table || null;
            this.forms = options.forms || [];
            this.bindEvents();
        },

        // Bind form submission events
        bindEvents: function() {
            const self = this;
            
            this.forms.forEach(function(formSelector) {
                $(formSelector).on('submit', function(e) {
                    e.preventDefault();
                    self.handleFormSubmission($(this));
                });
            });
        },

        // Handle AJAX form submission
        handleFormSubmission: function($form) {
            const self = this;
            const formData = new FormData($form[0]);
            const url = $form.attr('action');
            const modalId = $form.closest('.modal').attr('id');

            // Show loading state
            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.text();
            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Close modal
                    if (modalId) {
                        $(`#${modalId} .close[data-dismiss="modal"]`).trigger('click');
                    }
                    
                    // Reload table if specified
                    if (self.table && window[self.table]) {
                        window[self.table].ajax.reload(null, false);
                    }
                    
                    // Show toast with type from response or default to success
                    const toastType = response.type || 'success';
                    self.showToast(response.message, toastType);
                    
                    // Reset form
                    $form[0].reset();
                },
                error: function(xhr) {
                    let message = 'Có lỗi xảy ra!';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        // Handle validation errors
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        message = errors.join('<br>');
                    }
                    self.showToast(message, 'danger');
                },
                complete: function() {
                    // Reset button state
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        },

        // Toast notification function with 4 types: success, info, warning, danger
        showToast: function(message, type = 'info') {
            // Define toast configurations
            const toastConfig = {
                success: {
                    swalIcon: 'success',
                    title: 'Thành công!',
                    alertClass: 'alert-success',
                    icon: 'fa-check-circle',
                    bgColor: 'bg-success',
                    timer: 3000
                },
                info: {
                    swalIcon: 'info',
                    title: 'Thông tin!',
                    alertClass: 'alert-info',
                    icon: 'fa-info-circle',
                    bgColor: 'bg-info',
                    timer: 4000
                },
                warning: {
                    swalIcon: 'warning',
                    title: 'Cảnh báo!',
                    alertClass: 'alert-warning',
                    icon: 'fa-exclamation-triangle',
                    bgColor: 'bg-warning',
                    timer: 5000
                },
                danger: {
                    swalIcon: 'error',
                    title: 'Lỗi!',
                    alertClass: 'alert-danger',
                    icon: 'fa-exclamation-circle',
                    bgColor: 'bg-danger',
                    timer: 5000
                }
            };

            const config = toastConfig[type] || toastConfig.info;

            // Use SweetAlert2 if available
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: config.swalIcon,
                    title: config.title,
                    html: message,
                    timer: config.timer,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                    customClass: {
                        popup: `swal-toast-${type}`
                    }
                });
            } else {
                // Fallback to Bootstrap alert
                const textColor = type === 'warning' ? 'text-dark' : 'text-white';
                
                const alert = $(`
                    <div class="alert ${config.alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px; border-left: 4px solid;">
                        <i class="fas ${config.icon} mr-2"></i>
                        <span>${message}</span>
                        <button type="button" class="close ${textColor}" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `);
                
                $('body').append(alert);
                
                // Auto remove
                setTimeout(function() {
                    alert.alert('close');
                }, config.timer);
            }
        }
    };

    // Make it globally available
    window.AjaxFormHandler = AjaxFormHandler;
});

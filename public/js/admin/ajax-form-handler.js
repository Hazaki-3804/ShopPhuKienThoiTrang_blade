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
            this.onSuccess = options.onSuccess || null;
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

            // Bind cancel button events for all modals
            this.bindCancelEvents();
        },

        // Bind cancel button events
        bindCancelEvents: function() {
            const self = this;
            
            // Handle cancel buttons and modal close events
            $(document).on('click', '.modal [data-dismiss="modal"]', function() {
                const modal = $(this).closest('.modal');
                const modalId = modal.attr('id');
                const form = modal.find('form');
                
                if (form.length > 0) {
                    // Reset form
                    form[0].reset();
                    // Reset select elements to their first option
                    form.find('select').each(function() {
                        $(this).prop('selectedIndex', 0);
                    });
                    // Clear validation errors
                    self.clearFieldErrors(modalId);
                }
            });

            // Also handle when modal is hidden (ESC key, backdrop click, etc.)
            $(document).on('hidden.bs.modal', '.modal', function() {
                const modal = $(this);
                const modalId = modal.attr('id');
                const form = modal.find('form');
                
                if (form.length > 0) {
                    // Reset form
                    form[0].reset();
                    // Reset select elements to their first option
                    form.find('select').each(function() {
                        $(this).prop('selectedIndex', 0);
                    });
                    // Clear validation errors
                    self.clearFieldErrors(modalId);
                }
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
                    // Clear any previous validation errors
                    self.clearFieldErrors(modalId);
                    
                    // Close modal
                    if (modalId) {
                        $(`#${modalId} .close[data-dismiss="modal"]`).trigger('click');
                    }
                    
                    // Show toast with type from response or default to success
                    const toastType = response.type || 'success';
                    self.showToast(response.message, toastType);
                    
                    // Handle redirect if provided
                    if (response.redirect) {
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1500); // Delay để user thấy toast
                        return;
                    }
                    
                    // Reload table if specified
                    if (self.table && window[self.table]) {
                        window[self.table].ajax.reload(null, false);
                    }
                    
                    // Call onSuccess callback if provided
                    if (self.onSuccess && typeof self.onSuccess === 'function') {
                        self.onSuccess(response);
                    }
                    
                    // Reset form
                    $form[0].reset();
                },
                error: function(xhr) {
                    let message = 'Có lỗi xảy ra!';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    
                    // Handle validation errors - show them below input fields
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        self.showFieldErrors(xhr.responseJSON.errors, modalId);
                        // Still show general toast message
                        self.showToast(message, 'danger');
                    } else {
                        self.showToast(message, 'danger');
                    }
                },
                complete: function() {
                    // Reset button state
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        },

        // Show field-specific validation errors
        showFieldErrors: function(errors, modalId) {
            // Clear previous errors first
            this.clearFieldErrors(modalId);
            
            // Determine form type based on modal ID
            let formType = 'add';
            if (modalId && modalId.includes('edit')) {
                formType = 'edit';
            }
            
            // Show errors for each field
            Object.keys(errors).forEach(fieldName => {
                const errorDiv = $(`#${formType}_${fieldName}_error`);
                const inputField = $(`#${formType}_${fieldName}`);
                
                if (errorDiv.length && errors[fieldName].length > 0) {
                    errorDiv.html('<i class="fas fa-exclamation-triangle"></i> ' + errors[fieldName][0]).show();
                    inputField.addClass('is-invalid');
                }
            });
        },

        // Clear field validation errors
        clearFieldErrors: function(modalId) {
            let formType = 'add';
            if (modalId && modalId.includes('edit')) {
                formType = 'edit';
            }
            
            // Hide all error divs and remove invalid class
            $(`[id^="${formType}_"][id$="_error"]`).hide();
            $(`#${modalId} .form-control`).removeClass('is-invalid');
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

(function () {
    const baseUrl = document.body.dataset.baseUrl || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const endpoint = (path) => `${baseUrl}${path}`;

    const debounce = (fn, wait = 250) => {
        let timer;
        return (...args) => {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => fn(...args), wait);
        };
    };

    const showToast = (message, type = 'success') => {
        const existing = document.querySelector('.js-toast');
        existing?.remove();

        const toast = document.createElement('div');
        toast.className = `alert ${type} js-toast`;
        toast.textContent = message;
        document.querySelector('.page')?.prepend(toast);
        window.setTimeout(() => toast.remove(), 3000);
    };

    const postForm = async (path, formData) => {
        const response = await fetch(endpoint(path), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await response.json();
        if (!response.ok || !data.ok) {
            throw new Error(data.message || 'Request failed.');
        }
        return data;
    };

    document.querySelector('[data-nav-toggle]')?.addEventListener('click', () => {
        document.querySelector('[data-nav]')?.classList.toggle('open');
    });

    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    const clearClientErrors = (form) => {
        form.querySelectorAll('.client-error').forEach((node) => node.remove());
    };

    const clientError = (field, message) => {
        const error = document.createElement('small');
        error.className = 'client-error';
        error.textContent = message;
        field.insertAdjacentElement('afterend', error);
    };

    const validateRequired = (form, name, message) => {
        const field = form.elements[name];
        if (!field || String(field.value || '').trim() !== '') {
            return true;
        }
        clientError(field, message);
        return false;
    };

    const validators = {
        register(form) {
            let ok = true;
            ok = validateRequired(form, 'name', 'Name is required.') && ok;
            ok = validateRequired(form, 'address', 'Address is required.') && ok;
            ok = validateRequired(form, 'phone', 'Phone is required.') && ok;
            ok = validateEmail(form) && ok;
            ok = validatePassword(form, 'password', true) && ok;
            return ok;
        },
        login(form) {
            return validateEmail(form) && validateRequired(form, 'password', 'Password is required.');
        },
        profile(form) {
            let ok = true;
            ok = validateRequired(form, 'name', 'Name is required.') && ok;
            ok = validateRequired(form, 'address', 'Address is required.') && ok;
            ok = validateRequired(form, 'phone', 'Phone is required.') && ok;
            ok = validateEmail(form) && ok;
            const nextPassword = form.elements.new_password;
            if (nextPassword && nextPassword.value !== '') {
                ok = validatePassword(form, 'new_password', true) && ok;
                ok = validateRequired(form, 'current_password', 'Current password is required.') && ok;
            }
            return ok;
        },
        category(form) {
            let ok = true;
            ok = validateRequired(form, 'name', 'Category name is required.') && ok;
            ok = validateRequired(form, 'category_type', 'Choose liquid or solid.') && ok;
            return ok;
        },
        medicine(form) {
            let ok = true;
            ok = validateRequired(form, 'name', 'Medicine name is required.') && ok;
            ok = validateRequired(form, 'category_id', 'Choose a category.') && ok;
            ok = validateRequired(form, 'vendor_name', 'Vendor name is required.') && ok;
            ok = validatePositiveNumber(form, 'price', 'Price must be greater than 0.') && ok;
            ok = validateIntegerMin(form, 'availability', 0, 'Stock must be 0 or higher.') && ok;
            return ok;
        },
        checkout(form) {
            return validateRequired(form, 'shipping_address', 'Shipping address is required.');
        },
        payment(form) {
            const selected = form.querySelector('input[name="payment_method"]:checked');
            if (selected) {
                return true;
            }
            const first = form.querySelector('input[name="payment_method"]');
            clientError(first.closest('.payment-grid'), 'Select a payment method.');
            return false;
        },
    };

    const validateEmail = (form) => {
        const field = form.elements.email;
        if (!field || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value.trim())) {
            return true;
        }
        clientError(field, 'Enter a valid email address.');
        return false;
    };

    const validatePassword = (form, name, required) => {
        const field = form.elements[name];
        if (!field || (!required && field.value === '') || field.value.length >= 8) {
            return true;
        }
        clientError(field, 'Password must be at least 8 characters.');
        return false;
    };

    const validatePositiveNumber = (form, name, message) => {
        const field = form.elements[name];
        if (field && Number(field.value) > 0) {
            return true;
        }
        clientError(field, message);
        return false;
    };

    const validateIntegerMin = (form, name, min, message) => {
        const field = form.elements[name];
        if (field && Number.isInteger(Number(field.value)) && Number(field.value) >= min) {
            return true;
        }
        clientError(field, message);
        return false;
    };

    document.querySelectorAll('form[data-validate]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            clearClientErrors(form);
            const validator = validators[form.dataset.validate];
            if (validator && !validator(form)) {
                event.preventDefault();
            }
        });
    });

    const searchForm = document.querySelector('#medicineSearchForm');
    if (searchForm) {
        const runSearch = debounce(async () => {
            const params = new URLSearchParams(new FormData(searchForm));
            try {
                const response = await fetch(endpoint(`/api/medicines/search?${params.toString()}`), {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await response.json();
                document.querySelector('#medicineResults').innerHTML = data.html || '';
                document.querySelector('[data-search-count]').textContent = `${data.count} found`;
            } catch (error) {
                showToast(error.message, 'error');
            }
        });

        searchForm.addEventListener('input', runSearch);
        searchForm.addEventListener('change', runSearch);
        searchForm.addEventListener('submit', (event) => event.preventDefault());
    }

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-add-cart]');
        if (!form) {
            return;
        }

        event.preventDefault();
        const quantity = form.elements.quantity;
        const max = Number(quantity.max);
        const value = Number(quantity.value);

        if (!Number.isInteger(value) || value < 1 || value > max) {
            showToast('Choose a valid quantity within stock.', 'error');
            return;
        }

        try {
            const data = await postForm('/api/cart/add', new FormData(form));
            document.querySelectorAll('[data-cart-count]').forEach((node) => {
                node.textContent = data.cartCount;
            });
            showToast(data.message);
        } catch (error) {
            showToast(error.message, 'error');
        }
    });

    const updateCartPanel = (data) => {
        const panel = document.querySelector('#cartPanel');
        if (panel) {
            panel.innerHTML = data.html;
        }
        document.querySelectorAll('[data-cart-count]').forEach((node) => {
            node.textContent = data.cartCount;
        });
    };

    document.addEventListener('click', async (event) => {
        const stepButton = event.target.closest('[data-cart-step]');
        const removeButton = event.target.closest('[data-cart-remove]');
        const statusButton = event.target.closest('[data-order-action]');

        if (stepButton) {
            const row = stepButton.closest('[data-medicine-id]');
            const input = row.querySelector('[data-cart-quantity]');
            const next = Number(input.value) + Number(stepButton.dataset.cartStep);
            input.value = Math.max(Number(input.min), Math.min(Number(input.max), next));
            await submitCartQuantity(row, input.value);
        }

        if (removeButton) {
            const row = removeButton.closest('[data-medicine-id]');
            const formData = new FormData();
            formData.append('medicine_id', row.dataset.medicineId);
            try {
                updateCartPanel(await postForm('/api/cart/remove', formData));
            } catch (error) {
                showToast(error.message, 'error');
            }
        }

        if (statusButton) {
            const row = statusButton.closest('[data-order-id]');
            const formData = new FormData();
            formData.append('order_id', row.dataset.orderId);
            formData.append('status', statusButton.dataset.orderAction);
            try {
                const data = await postForm('/api/orders/status', formData);
                const status = row.querySelector('[data-order-status]');
                status.textContent = data.status;
                status.className = `status ${data.status}`;
                showToast(data.message);
            } catch (error) {
                showToast(error.message, 'error');
            }
        }
    });

    document.addEventListener('change', async (event) => {
        const input = event.target.closest('[data-cart-quantity]');
        if (!input) {
            return;
        }

        const value = Number(input.value);
        if (!Number.isInteger(value) || value < Number(input.min) || value > Number(input.max)) {
            showToast('Quantity must be within available stock.', 'error');
            return;
        }

        await submitCartQuantity(input.closest('[data-medicine-id]'), input.value);
    });

    const submitCartQuantity = async (row, quantity) => {
        const formData = new FormData();
        formData.append('medicine_id', row.dataset.medicineId);
        formData.append('quantity', quantity);

        try {
            updateCartPanel(await postForm('/api/cart/update', formData));
        } catch (error) {
            showToast(error.message, 'error');
        }
    };
})();

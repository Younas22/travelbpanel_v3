
    <script src="{{ url('public/assets/libs/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const themeIcon = document.getElementById('theme-icon');

            if (html.getAttribute('data-bs-theme') === 'dark') {
                html.setAttribute('data-bs-theme', 'light');
                themeIcon.className = 'bi bi-moon-fill';
            } else {
                html.setAttribute('data-bs-theme', 'dark');
                themeIcon.className = 'bi bi-sun-fill';
            }
        }

        // Mobile Sidebar Toggle Function
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            if (!overlay) {
                // Create overlay if it doesn't exist
                const newOverlay = document.createElement('div');
                newOverlay.className = 'sidebar-overlay';
                newOverlay.addEventListener('click', toggleSidebar);
                document.body.appendChild(newOverlay);
            }

            sidebar.classList.toggle('open');

            const existingOverlay = document.querySelector('.sidebar-overlay');
            if (existingOverlay) {
                existingOverlay.classList.toggle('active');
            }
        }

        // Initialize Bootstrap dropdowns
        document.addEventListener('DOMContentLoaded', function() {
            // Enable all Bootstrap dropdowns
            var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
        });

        // Select all checkbox functionality
        // document.querySelector('thead input[type="checkbox"]').addEventListener('change', function() {
        //     const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
        //     checkboxes.forEach(checkbox => {
        //         checkbox.checked = this.checked;
        //     });
        // });
    </script>



<script>
        // File upload drag and drop functionality
        document.querySelectorAll('.file-upload-area').forEach(area => {
            area.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            area.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });

            area.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                // Handle file drop logic here
            });
        });

        // File input change handlers
        // document.getElementById('logoUpload').addEventListener('change', function(e) {
        //     if (e.target.files.length > 0) {
        //         console.log('Logo file selected:', e.target.files[0].name);
        //     }
        // });

        // document.getElementById('faviconUpload').addEventListener('change', function(e) {
        //     if (e.target.files.length > 0) {
        //         console.log('Favicon file selected:', e.target.files[0].name);
        //     }
        // });

        // document.getElementById('coverUpload').addEventListener('change', function(e) {
        //     if (e.target.files.length > 0) {
        //         console.log('Cover file selected:', e.target.files[0].name);
        //     }
        // });
    </script>


<script>
        function toggleConfig(paymentMethod) {
            const configForm = document.getElementById(paymentMethod + 'Config');
            
            if (configForm.style.display === 'none' || configForm.style.display === '') {
                // Close all other config forms
                document.querySelectorAll('.config-form').forEach(form => {
                    form.style.display = 'none';
                });
                
                // Show the selected config form
                configForm.style.display = 'block';
                
                // Smooth scroll to the form
                setTimeout(() => {
                    configForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 100);
            } else {
                configForm.style.display = 'none';
            }
        }

        // Update status badges based on toggle switches
        function updateStatusBadge(paymentMethod) {
            const toggle = document.getElementById(paymentMethod + 'Status');
            const badge = toggle.closest('.payment-method-card').querySelector('.status-badge');
            
            if (toggle.checked) {
                badge.className = 'status-badge status-active';
                badge.textContent = 'Active';
            } else {
                badge.className = 'status-badge status-inactive';
                badge.textContent = 'Inactive';
            }
        }

        // Add event listeners for status toggles
        document.addEventListener('DOMContentLoaded', function() {
            const toggles = ['stripe', 'paypal', 'razorpay', 'square'];
            
            toggles.forEach(method => {
                const toggle = document.getElementById(method + 'Status');
                if (toggle) {
                    toggle.addEventListener('change', () => updateStatusBadge(method));
                }
            });
        });

        // Handle form submissions
        document.querySelectorAll('.config-form form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get payment method name from the form's parent ID
                const configFormId = this.closest('.config-form').id;
                const paymentMethod = configFormId.replace('Config', '');
                
                // Simulate saving
                setTimeout(() => {
                    alert(`${paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1)} configuration saved successfully!`);
                    
                    // Update status badge to configured
                    const badge = this.closest('.payment-method-card').querySelector('.status-badge');
                    badge.className = 'status-badge status-configured';
                    badge.textContent = 'Configured';
                    
                    // Hide the config form
                    this.closest('.config-form').style.display = 'none';
                }, 500);
            });
        });
    </script>


<script>
        // Select all checkbox functionality
        // const selectAllCheckbox = document.querySelector('thead input[type="checkbox"]');
        // if (selectAllCheckbox) {
        //     selectAllCheckbox.addEventListener('change', function() {
        //         const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
        //         checkboxes.forEach(checkbox => {
        //             checkbox.checked = this.checked;
        //         });
        //     });
        // }

        // // Handle add subscriber form
        // document.getElementById('addSubscriberForm').addEventListener('submit', function(e) {
        //     e.preventDefault();
            
        //     // Simulate adding subscriber
        //     setTimeout(() => {
        //         alert('Subscriber added successfully!');
        //         bootstrap.Modal.getInstance(document.getElementById('addSubscriberModal')).hide();
        //     }, 500);
        // });

        // Handle file upload
        // document.getElementById('csvFile').addEventListener('change', function(e) {
        //     if (e.target.files.length > 0) {
        //         console.log('CSV file selected:', e.target.files[0].name);
        //     }
        // });

        // Drag and drop functionality
        const importArea = document.querySelector('.import-area');
        if (importArea) {
            importArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            importArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });

            importArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                // Handle file drop logic here
            });
        }
    </script>



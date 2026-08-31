        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register(<?php echo json_encode(base_url('sw.js')); ?>, {
                scope: <?php echo json_encode(rtrim(base_url(), '/') . '/'); ?>
            }).catch(function () {});
        });
    }
    </script>
</body>
</html>

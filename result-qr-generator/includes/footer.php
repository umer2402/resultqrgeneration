<?php if (($layout ?? 'admin') === 'admin'): ?>
            </main>
            <footer class="app-footer">
                <p class="mb-0">&copy; <?= date('Y'); ?> <?= e(APP_INSTITUTE); ?>. Result QR verification system.</p>
            </footer>
        </div>
    </div>
<?php else: ?>
        </main>
    </div>
<?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="<?= e(url('assets/js/script.js')); ?>"></script>
</body>
</html>

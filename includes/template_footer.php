<?php
/**
 * Footer común para dashboards.
 */
?>
<footer class="glass-footer">
    <img src="<?php echo obtener_ruta_base(); ?>imagenes/gob.png" alt="Gob" style="height:18px;opacity:0.4;filter:grayscale(1);margin:0 6px;">
    <strong>UNEFA</strong> | Excelencia Educativa Abierta al Pueblo
    <img src="<?php echo obtener_ruta_base(); ?>imagenes/200.png" alt="200" style="height:18px;opacity:0.4;filter:grayscale(1);margin:0 6px;">
    <br>
    Vicerrectorado de Investigación, Postgrado y Recreación<br>
    SIP-Postgrado 2026 &middot; Todos los derechos reservados
</footer>

<!-- Particles canvas -->
<canvas id="particles-canvas"></canvas>

<script>
// ===== GLOBAL INTERACTIVE JS =====

// Sidebar collapse toggle
(function() {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.sidebar-toggle');
    if (sidebar && !toggleBtn) {
        const btn = document.createElement('button');
        btn.className = 'sidebar-toggle';
        btn.innerHTML = '&#9664;';
        btn.title = 'Colapsar menú';
        btn.onclick = function() {
            sidebar.classList.toggle('collapsed');
            btn.classList.toggle('collapsed');
        };
        document.body.appendChild(btn);
    }
})();

// Toast notification helper
function mostrarToast(mensaje, tipo) {
    tipo = tipo || 'success';
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + tipo;
    toast.textContent = mensaje;
    container.appendChild(toast);
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(40px)';
        toast.style.transition = 'opacity 0.3s, transform 0.3s';
        setTimeout(function() { toast.remove(); }, 300);
    }, 3500);
}

// Module tab switching
function mostrarModulo(modulo) {
    document.querySelectorAll('.module-section').forEach(function(s) { s.style.display = 'none'; });
    var el = document.getElementById('modulo-' + modulo);
    if (el) { el.style.display = 'block'; el.style.animation = 'none'; setTimeout(function() { el.style.animation = ''; }, 10); }
    document.querySelectorAll('.sidebar-menu a').forEach(function(a) { a.classList.remove('active'); });
    document.querySelectorAll('.sidebar-menu a').forEach(function(a) {
        var text = a.textContent.trim().toLowerCase();
        var classes = a.className;
        if (text.indexOf(modulo) !== -1 || a.getAttribute('data-modulo') === modulo) a.classList.add('active');
    });
    if (window.location.hash !== '#modulo-' + modulo) {
        window.location.hash = 'modulo-' + modulo;
    }
}

// Hash-based module navigation on load
(function() {
    var hash = window.location.hash.replace('#modulo-', '');
    if (hash && document.getElementById('modulo-' + hash)) {
        setTimeout(function() { mostrarModulo(hash); }, 100);
    }
})();

// Confirm dialog helper
function confirmar(mensaje) {
    return confirm(mensaje || '¿Está seguro de realizar esta acción?');
}

// Auto-dismiss alerts after 6 seconds
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.alert').forEach(function(el) {
        setTimeout(function() {
            el.style.transition = 'opacity 0.5s, max-height 0.5s, padding 0.3s, margin 0.3s';
            el.style.opacity = '0';
            el.style.maxHeight = '0';
            el.style.padding = '0';
            el.style.margin = '0';
            el.style.overflow = 'hidden';
            el.style.border = 'none';
            setTimeout(function() { el.remove(); }, 500);
        }, 6000);
    });

    // Scroll reveal animation
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.form-section, .table-section, .card').forEach(function(el) {
        if (!el.classList.contains('card')) {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(el);
        }
    });

    // Staggered entrance for table rows
    document.querySelectorAll('.data-table tbody tr').forEach(function(row, i) {
        row.style.opacity = '0';
        row.style.transform = 'translateX(-10px)';
        row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        setTimeout(function() {
            row.style.opacity = '1';
            row.style.transform = 'translateX(0)';
        }, 50 + i * 40);
    });
});

// ===== PARTICLES BACKGROUND =====
(function() {
    var canvas = document.getElementById('particles-canvas');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    var particles = [];
    var count = 50;

    function resize() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    for (var i = 0; i < count; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            r: Math.random() * 2 + 0.5,
            dx: (Math.random() - 0.5) * 0.4,
            dy: (Math.random() - 0.5) * 0.4,
            o: Math.random() * 0.3 + 0.1
        });
    }

    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        particles.forEach(function(p) {
            p.x += p.dx;
            p.y += p.dy;
            if (p.x < 0 || p.x > canvas.width) p.dx *= -1;
            if (p.y < 0 || p.y > canvas.height) p.dy *= -1;
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255, 255, 255, ' + p.o + ')';
            ctx.fill();
        });
        requestAnimationFrame(animate);
    }
    animate();
})();

// ===== COUNTER ANIMATION =====
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.count-up').forEach(function(el) {
        var target = parseInt(el.textContent.replace(/[^0-9]/g, '')) || 0;
        var current = 0;
        var step = Math.max(1, Math.floor(target / 30));
        var timer = setInterval(function() {
            current += step;
            if (current >= target) { current = target; clearInterval(timer); }
            el.textContent = current;
        }, 30);
    });
});
</script>
</body>
</html>
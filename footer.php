<!-- Footer content -->
<footer class="bg-dark text-white py-1 mt-1">
    <div class="container">
        <div class="row">
            <!-- Left Side - Church Name -->
            <div class="col-md-6 text-md-start mb-2 mb-md-0">
                <h6 class="mb-0">&copy; <?= date("Y"); ?> Church Name - Requisition System</h6>
            </div>

            <!-- Right Side - Social Media Icons -->
            <div class="col-md-6 text-md-end">
                <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </div>
</footer>

<style>
    footer {
    background: linear-gradient(135deg, #343a40, #212529); /* Gradient for a modern look */
    box-shadow: 0px -3px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow for depth */
}

footer h6 {
    font-weight: bold;
    font-size: 14px; 
}

footer a {
    font-size: 20px; /* Icon size */
}

footer a:hover {
    color: #f8f9fa; /* Hover effect for icons */
    text-decoration: none;
}

</style>

<!-- Add FontAwesome for icons -->
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

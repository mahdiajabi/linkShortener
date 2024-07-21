<!DOCTYPE html>
<html>
<head>
    <title>Redirecting...</title>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            fetch(window.location.href)
                .then(response => response.json())
                .then(data => {
                    if (data.url) {
                        window.location.href = data.url;
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    </script>
</head>
<body>
    <p>Redirecting...</p>
</body>
</html>

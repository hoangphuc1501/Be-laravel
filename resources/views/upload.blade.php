<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Ảnh lên Cloudinary</title>
</head>
<body>
    <h2>Upload Ảnh lên Cloudinary</h2>
    <form action="{{ url('/upload-cloudinary') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="image" required>
        <button type="submit">Upload</button>
    </form>
</body>
</html>

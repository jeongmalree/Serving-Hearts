<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aligned Containers with Flexbox</title>
    <style>
        .container-wrapper {
            display: flex; /* This makes the children (divs) align next to each other */
            justify-content: space-between; /* Distributes space between the items */
            gap: 20px; /* Adds spacing between the divs */
        }

        .container {
            flex: 1; /* Make containers grow to fill available space */
            padding: 20px;
            border: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Subtle shadow */
            background-color: #f0f0f0;
            border-radius: 5px;
        }

        .container-1 {
            background-color: #d1e7ff;
        }
        .container-2 {
            background-color: #ffdfd1;
        }
    </style>
</head>
<body>

<div class="container-wrapper">
    <div class="container container-1">
        <h2>First Container</h2>
        <p>This is the first container.</p>
    </div>

    <div class="container container-2">
        <h2>Second Container</h2>
        <p>This is the second container.</p>
    </div>

    <div class="container">
        <h2>Third Container</h2>
        <p>This is a generic container.</p>
    </div>
</div>

</body>
</html>

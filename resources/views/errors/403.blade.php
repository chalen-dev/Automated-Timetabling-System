<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>401 Unauthorized</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #ffffff;
            font-family: "Poppins", sans-serif;
            color: #333;
            position: relative;
            overflow: hidden;
        }

        svg.bg-shape {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 250px;
            z-index: -1;
        }

        .error-page {
            text-align: center;
            background: #fff;
            padding: 40px 60px;
            border-radius: 14px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
        }

        .error-page h1 {
            font-size: 100px;
            margin: 0;
            font-weight: 700;
            color: #5e0b0b;
        }

        .error-page p {
            font-size: 16px;
            margin: 10px 0 20px;
            color: #555;
        }

        .error-page a {
            display: inline-block;
            padding: 10px 20px;
            background: #5e0b0b;
            color: #fff;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s ease-in-out;
        }

        .error-page a:hover {
            background: #fff;
            color: #5e0b0b;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>
    <body>
        <svg
            class="bg-shape"
            viewBox="0 0 1440 468"
            xmlns="http://www.w3.org/2000/svg"
            preserveAspectRatio="none"
        >
            <path
                d="M1488.5 -35.5V280.518L-20.5 467.434V-35.5H1488.5Z"
                fill="#5E0B0B"
            ></path>
        </svg>

        <div class="error-page">
            <title>403 Forbidden</title>
            <h1>403</h1>
            <p>This action is unauthorized.</p>
            <a href="{{ route('login') }}">Go Back </a>
        </div>
    </body>
</html>

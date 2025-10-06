<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - IronFlex Gym</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Montserrat:wght@700&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

  <style>
    /* --- THEME COLORS MATCHING ADMIN/TRAINER DASHBOARD --- */
    :root {
      --accent-red: #E63946;
      --accent-gold: #FFD166;
      --text-light: #F5F5F5;
      --bg-dark: #121212;
      --card-bg: rgba(26, 26, 26, 0.95);
      --input-bg: #161616;
      --input-border: #444;
    }

    /* Base */
    body {
      font-family: 'Poppins', sans-serif;
      background: var(--bg-dark);
      color: var(--text-light);
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
    }

    /* Container */
    .about-container {
      background: var(--card-bg);
      backdrop-filter: blur(15px);
      border: 1px solid var(--input-border);
      border-radius: 16px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.8);
      padding: 60px;
      max-width: 1100px;
      margin: 60px auto;
      animation: fadeIn 1s ease-in-out;
    }

    /* Headings */
    h1, h2 {
      text-align: center;
      letter-spacing: 1px;
      margin-bottom: 20px;
    }

    h1 {
      font-family: 'Montserrat', sans-serif;
      font-size: 3rem;
      color: var(--accent-gold);
      margin-bottom: 25px;
      border-bottom: 2px solid var(--accent-red);
      display: inline-block;
      padding-bottom: 10px;
    }

    h2 {
      font-size: 2rem;
      color: var(--accent-red);
      margin: 50px 0 25px;
      position: relative;
    }

    h2::after {
      content: "";
      width: 80px;
      height: 3px;
      background: var(--accent-gold);
      display: block;
      margin: 10px auto 0;
      border-radius: 2px;
    }

    /* Text */
    p {
      line-height: 1.8;
      font-size: 1.05rem;
      margin-bottom: 20px;
      text-align: center;
      color: #ddd;
    }

    /* Unique Features Section */
    .unique-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 25px;
      margin-top: 30px;
    }

    .unique-item {
      background: rgba(255, 255, 255, 0.05);
      padding: 30px 20px;
      border-radius: 16px;
      text-align: center;
      transition: transform 0.4s ease, box-shadow 0.4s ease;
      opacity: 0;
      transform: translateY(40px);
      border: 1px solid var(--input-border);
    }

    .unique-item h3 {
      font-family: 'Montserrat', sans-serif;
      font-size: 1.4rem;
      margin-bottom: 12px;
      color: var(--accent-gold);
    }

    .unique-item:hover {
      transform: translateY(-10px) scale(1.05);
      box-shadow: 0px 12px 25px rgba(0,0,0,0.6);
      border-color: var(--accent-red);
    }

    /* Team Section */
    .team-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 30px;
      margin-top: 30px;
    }

    .team-member {
      position: relative;
      border-radius: 16px;
      overflow: hidden;
      cursor: pointer;
      transition: transform 0.4s ease, box-shadow 0.4s ease;
      border: 1px solid var(--input-border);
    }

    .team-member img {
      width: 100%;
      height: 320px;
      object-fit: cover;
      border-radius: 16px;
      transition: transform 0.4s ease;
    }

    .team-member:hover img {
      transform: scale(1.1);
    }

    .overlay {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 100%;
      background: rgba(0,0,0,0.8);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 20px;
      opacity: 0;
      transform: translateY(100%);
      transition: all 0.5s ease;
      text-align: center;
    }

    .team-member:hover .overlay {
      opacity: 1;
      transform: translateY(0);
    }

    .overlay h4 {
      font-family: 'Montserrat', sans-serif;
      color: var(--accent-gold);
      font-size: 1.4rem;
      margin-bottom: 8px;
    }

    .overlay p {
      font-size: 1rem;
      color: var(--accent-red);
      margin-bottom: 10px;
    }

    .overlay span {
      font-size: 0.9rem;
      color: #ccc;
    }

    /* Animation */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

  <div class="about-container">
    <h1>About FitX Gym</h1>
    <p>
      Welcome to <strong>FitX Gym</strong> — where strength, health, and transformation come together.  
      Our mission is to empower every member with expert training, personalized nutrition, and modern tools 
      to achieve their fitness goals.
    </p>

    <h2>What Makes Us Unique</h2>
    <div class="unique-list">
      <div class="unique-item">
        <h3><i class="fas fa-dumbbell"></i> Certified Trainers</h3>
        <p>Guidance from certified experts who specialize in fitness, strength, and conditioning.</p>
      </div>
      <div class="unique-item">
        <h3><i class="fas fa-utensils"></i> Personalized Diet Plans</h3>
        <p>Nutrition plans tailored for your body type and fitness goals.</p>
      </div>
      <div class="unique-item">
        <h3><i class="fas fa-calendar-alt"></i> Gym Calendar</h3>
        <p>Organized training schedules, classes, and events to keep you consistent.</p>
      </div>
    </div>

    <h2>Meet the Team</h2>
    <div class="team-grid">
      <div class="team-member">
        <img src="images/trainer2.jpg" alt="Nutritionist">
        <div class="overlay">
          <h4>Sarah Johnson</h4>
          <p>Certified Nutritionist</p>
          <span>Specializes in creating personalized diet plans for weight loss, muscle gain, and overall health.</span>
        </div>
      </div>
      <div class="team-member">
        <img src="images/trainer1.jpg" alt="Muscle Gain Trainer">
        <div class="overlay">
          <h4>John Smith</h4>
          <p>Muscle Gain Specialist</p>
          <span>Designs progressive strength training programs to help members safely increase muscle mass.</span>
        </div>
      </div>
      <div class="team-member">
        <img src="images/trainer3.jpg" alt="Weight Loss Trainer">
        <div class="overlay">
          <h4>Emily Davis</h4>
          <p>Weight Loss & Cardio</p>
          <span>Focuses on sustainable weight loss programs combining cardio, strength, and functional workouts.</span>
        </div>
      </div>
      <div class="team-member">
        <img src="images/diet.jpg" alt="General Fitness Trainer">
        <div class="overlay">
          <h4>Mike Lee</h4>
          <p>General Fitness Coach</p>
          <span>Provides well-rounded fitness coaching with endurance, flexibility, and strength balance.</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Scroll reveal effect -->
  <script>
    function revealOnScroll() {
      const elements = document.querySelectorAll('.unique-item, .team-member');
      const triggerBottom = window.innerHeight * 0.85;

      elements.forEach(el => {
        const boxTop = el.getBoundingClientRect().top;
        if (boxTop < triggerBottom) {
          el.style.opacity = '1';
          el.style.transform = 'translateY(0)';
          el.style.transition = 'all 0.7s ease-out';
        }
      });
    }

    window.addEventListener('scroll', revealOnScroll);
    revealOnScroll();
  </script>
</body>
</html>

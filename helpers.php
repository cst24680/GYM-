<?php
function getBMICategory($bmi) {
    if ($bmi < 18.5) return 'Underweight';
    if ($bmi < 25)   return 'Normal';
    if ($bmi < 30)   return 'Overweight';
    return 'Obese'; // optional, in case you want a stricter category
}

function getAgeCategory($age) {
    if ($age < 25) return 'Young';
    if ($age <= 40) return 'Adult';
    return 'Senior';
}

function findTemplate($conn, $goal, $bmiCat, $ageCat, $dietType) {
    $goalEsc = mysqli_real_escape_string($conn, $goal);
    $dietEsc = mysqli_real_escape_string($conn, $dietType);

    // 1) Exact match
    $q = "SELECT * FROM diet_templates
          WHERE Goal='$goalEsc' 
            AND BMI_Category='$bmiCat' 
            AND Age_Category='$ageCat' 
            AND Diet_type='$dietEsc'
          LIMIT 1";
    $res = mysqli_query($conn, $q);
    if ($res && mysqli_num_rows($res) > 0) return mysqli_fetch_assoc($res);

    // 2) Relax Age
    $q = "SELECT * FROM diet_templates
          WHERE Goal='$goalEsc' 
            AND BMI_Category='$bmiCat' 
            AND (Age_Category='$ageCat' OR Age_Category='Any')
            AND Diet_type='$dietEsc' 
          LIMIT 1";
    $res = mysqli_query($conn, $q);
    if ($res && mysqli_num_rows($res) > 0) return mysqli_fetch_assoc($res);

    // 3) Relax BMI
    $q = "SELECT * FROM diet_templates
          WHERE Goal='$goalEsc' 
            AND (BMI_Category='$bmiCat' OR BMI_Category='Any')
            AND (Age_Category='$ageCat' OR Age_Category='Any')
            AND Diet_type='$dietEsc' 
          LIMIT 1";
    $res = mysqli_query($conn, $q);
    if ($res && mysqli_num_rows($res) > 0) return mysqli_fetch_assoc($res);

    // 4) Fallback (any diet template for this goal)
    $q = "SELECT * FROM diet_templates WHERE Goal='$goalEsc' LIMIT 1";
    $res = mysqli_query($conn, $q);
    if ($res && mysqli_num_rows($res) > 0) return mysqli_fetch_assoc($res);

    return null;
}
?>

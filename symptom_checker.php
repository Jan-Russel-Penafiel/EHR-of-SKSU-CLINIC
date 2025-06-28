<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['GmailAccount'])) {
    header('Location: login.html'); // Redirect to login page if not logged in
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize variables
$symptoms = "";
$medications = ""; // Initialize the medications variable
$suggestions = [];  // Initialize the suggestions array

// After analyzing the symptoms and generating suggestions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the symptoms and medications inputs from the form
    $symptoms = trim($_POST['symptoms']);
    $medications = isset($_POST['medications']) ? trim($_POST['medications']) : ''; // Check if medications are provided

    if (!empty($symptoms)) {
        // Analyzing symptoms (replace with your actual symptom analysis logic)
        $suggestions = analyzeSymptoms($symptoms);

        // Prepare and bind the SQL statement
        $stmt = $conn->prepare("INSERT INTO symptom_analysis (user_email, symptoms, medications, suggestions) VALUES (?, ?, ?, ?)");
        $user_email = $_SESSION['GmailAccount']; // Assuming you store user email in session
        $suggestionText = implode(", ", array_column($suggestions, 'condition')); // Convert array of conditions to string

        $stmt->bind_param("ssss", $user_email, $symptoms, $medications, $suggestionText); // Bind the parameters

        // Execute the statement
        if ($stmt->execute()) {
            echo "Symptoms analyzed and saved successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close(); // Close the statement
    } else {
        $suggestions = [["condition" => "Please enter some symptoms to analyze.", "definition" => ""]];
    }
}



// Function to analyze symptoms and suggest conditions
function analyzeSymptoms($symptoms) {
    // Define possible conditions based on symptoms and their definitions
    $conditions = [
        // Respiratory and Viral Infections
      'Common Cold' => [
    'definition' => 'A viral infection of the upper respiratory tract characterized by sneezing, runny nose, and sore throat.',
    'symptoms' => ['runny nose', 'sore throat', 'sneezing', 'slight fever', 'cough', 'mild headache', 'fatigue'],
    'medications' => ['Decongestants', 'Antihistamines', 'Paracetamol for fever']
],
'Flu' => [
    'definition' => 'An infectious disease caused by the influenza virus, marked by fever, body aches, and fatigue.',
    'symptoms' => ['high fever', 'chills', 'body aches', 'extreme tiredness', 'sudden onset', 'sore throat', 'dry cough'],
    'medications' => ['Antiviral drugs (e.g., oseltamivir)', 'Paracetamol', 'Ibuprofen']
],
'COVID-19' => [
    'definition' => 'A highly contagious viral infection caused by the coronavirus SARS-CoV-2, affecting the respiratory system.',
    'symptoms' => ['fever', 'dry cough', 'lost taste', 'breathing trouble', 'fatigue', 'muscle aches', 'sore throat', 'headache'],
    'medications' => ['Antiviral medications', 'Symptom relief (e.g., paracetamol)', 'Vitamin D']
],
'Viral Infection' => [
    'definition' => 'A general term for infections caused by viruses, often characterized by flu-like symptoms.',
    'symptoms' => ['low fever', 'body aches', 'sore throat', 'tiredness', 'headache', 'chills', 'loss of appetite'],
    'medications' => ['Rest', 'Fluids', 'Pain relievers (e.g., ibuprofen)']
],
'RSV (Respiratory Syncytial Virus)' => [
    'definition' => 'A common respiratory virus that usually causes mild, cold-like symptoms but can lead to severe respiratory infections.',
    'symptoms' => ['coughing', 'wheezing', 'trouble breathing', 'runny nose', 'fever', 'reduced appetite', 'irritability'],
    'medications' => ['Saline nasal spray', 'Bronchodilators', 'Oxygen therapy in severe cases']
],
'Pneumonia' => [
    'definition' => 'An infection that inflames the air sacs in one or both lungs, which may fill with fluid.',
    'symptoms' => ['sharp chest pain', 'coughing up mucus', 'high fever', 'shortness of breath', 'fatigue', 'chills'],
    'medications' => ['Antibiotics (if bacterial)', 'Cough suppressants', 'Oxygen therapy']
],
'Bronchitis' => [
    'definition' => 'Inflammation of the bronchial tubes, which carry air to and from the lungs, often due to infection.',
    'symptoms' => ['long cough', 'tight chest', 'wheezing', 'fatigue', 'mild fever', 'sore throat'],
    'medications' => ['Cough suppressants', 'Bronchodilators', 'NSAIDs for inflammation']
],
'Asthma' => [
    'definition' => 'A chronic condition that causes inflammation and narrowing of the airways, making breathing difficult.',
    'symptoms' => ['breathing trouble', 'tight chest', 'worse at night', 'wheezing', 'coughing', 'shortness of breath'],
    'medications' => ['Inhaled corticosteroids', 'Short-acting bronchodilators (e.g., albuterol)', 'Montelukast']
],
'Cough' => [
    'definition' => 'A reflex action to clear the throat and airway of mucus or foreign irritants.',
    'symptoms' => ['cough', 'scratchy throat', 'worse in cold', 'mucus production', 'sore chest', 'hoarseness'],
    'medications' => ['Cough syrups', 'Lozenges', 'Honey (for soothing)']
],
'Sinusitis' => [
    'definition' => 'Inflammation or swelling of the tissue lining the sinuses, often causing blockage and infection.',
    'symptoms' => ['face pressure', 'headache', 'thick mucus', 'nasal congestion', 'tooth pain', 'cough'],
    'medications' => ['Decongestants', 'Saline sprays', 'Antibiotics if bacterial']
],

// Gastrointestinal Issues
'Diarrhea' => [
    'definition' => 'Frequent loose or watery bowel movements, often caused by infection or digestive issues.',
    'symptoms' => ['loose stools', 'stomach cramps', 'urgency to go', 'nausea', 'vomiting', 'dehydration'],
    'medications' => ['Oral rehydration salts (ORS)', 'Loperamide', 'Probiotics']
],
'Food Poisoning' => [
    'definition' => 'Illness caused by eating contaminated food, leading to gastrointestinal symptoms.',
    'symptoms' => ['vomiting', 'stomach pain', 'sudden diarrhea', 'nausea', 'fever', 'muscle aches'],
    'medications' => ['ORS', 'Antiemetics', 'Antibiotics (if bacterial)']
],
'Indigestion' => [
    'definition' => 'Discomfort or pain in the stomach associated with difficulty in digesting food.',
    'symptoms' => ['stomach burning', 'bloating', 'burping', 'nausea', 'feeling full quickly'],
    'medications' => ['Antacids', 'Proton pump inhibitors', 'Simethicone']
],
'Gastroenteritis' => [
    'definition' => 'Inflammation of the stomach and intestines, often caused by viral or bacterial infections.',
    'symptoms' => ['loose stools', 'upset stomach', 'slight fever', 'nausea', 'vomiting', 'cramps'],
    'medications' => ['ORS', 'Antiemetics', 'Probiotics']
],
'Hyperacidity/Acid Reflux' => [
    'definition' => 'A condition in which stomach acid frequently flows back into the esophagus, causing irritation.',
    'symptoms' => ['chest pain after eating', 'sour taste', 'worse lying down', 'nausea', 'difficulty swallowing'],
    'medications' => ['Antacids', 'H2 blockers', 'Proton pump inhibitors']
],
'Stomachache' => [
    'definition' => 'Pain or discomfort in the abdomen, which can have various causes.',
    'symptoms' => ['stomach pain', 'bloating', 'relief after gas', 'nausea', 'cramps', 'loss of appetite'],
    'medications' => ['Simethicone', 'Antispasmodics', 'Peppermint oil']
],

       // Skin Conditions
'Eczema' => [
    'definition' => 'A condition that causes the skin to become inflamed, itchy, and red.',
    'symptoms' => ['dry skin', 'itchy patches', 'red spots', 'thickened skin', 'scaly areas', 'swelling'],
    'medications' => ['Moisturizers', 'Topical corticosteroids', 'Antihistamines']
],
'Ringworm' => [
    'definition' => 'A fungal infection that creates a ring-like red rash on the skin.',
    'symptoms' => ['ring rash', 'itchy', 'clearing center', 'redness', 'scaling', 'bump'],
    'medications' => ['Antifungal creams', 'Oral antifungals', 'Clotrimazole']
],
'Athlete\'s Foot' => [
    'definition' => 'A fungal infection that usually begins between the toes, causing itching and burning.',
    'symptoms' => ['itchy toes', 'blisters', 'peeling skin', 'cracking skin', 'redness', 'burning sensation'],
    'medications' => ['Antifungal powders', 'Topical antifungals', 'Tolnaftate']
],
'Acne' => [
    'definition' => 'A skin condition that occurs when hair follicles become clogged with oil and dead skin cells.',
    'symptoms' => ['pimples', 'red spots', 'painful bumps', 'blackheads', 'whiteheads', 'oily skin'],
    'medications' => ['Benzoyl peroxide', 'Salicylic acid', 'Topical retinoids']
],
'Cold Sores' => [
    'definition' => 'Blisters that typically appear on the lips or around the mouth caused by the herpes simplex virus.',
    'symptoms' => ['blisters on lips', 'tingling', 'crusting', 'redness', 'pain', 'sensitivity'],
    'medications' => ['Antiviral creams', 'Acyclovir', 'Lysine supplements']
],
'Chickenpox' => [
    'definition' => 'A highly contagious viral infection causing an itchy rash and flu-like symptoms.',
    'symptoms' => ['itchy spots', 'fever', 'loss of appetite', 'fatigue', 'headache', 'sore throat'],
    'medications' => ['Calamine lotion', 'Antihistamines', 'Paracetamol']
],
'Hand, Foot, and Mouth Disease' => [
    'definition' => 'A viral illness common in young children, characterized by sores in the mouth and a rash on hands and feet.',
    'symptoms' => ['red spots on hands and feet', 'mouth sores', 'slight fever', 'irritability', 'sore throat'],
    'medications' => ['Pain relievers (e.g., paracetamol)', 'Topical anesthetics', 'Hydration']
],

// Ear, Nose, and Throat Conditions
'Allergy' => [
    'definition' => 'An immune response to a substance that is usually harmless, resulting in allergy symptoms.',
    'symptoms' => ['sneezing', 'watery eyes', 'runny nose', 'itchy throat', 'nasal congestion', 'fatigue'],
    'medications' => ['Antihistamines (e.g., loratadine)', 'Nasal sprays', 'Decongestants']
],
'Seasonal Allergies' => [
    'definition' => 'Allergic reactions that occur at certain times of the year, often triggered by pollen.',
    'symptoms' => ['runny nose outdoors', 'itchy eyes', 'drip down throat', 'sneezing', 'coughing', 'fatigue'],
    'medications' => ['Antihistamines', 'Allergy eye drops', 'Nasal corticosteroids']
],
'Conjunctivitis (Pink Eye)' => [
    'definition' => 'An inflammation of the thin clear tissue that lines the eyelid and covers the white part of the eyeball.',
    'symptoms' => ['red eyes', 'eye discharge', 'gritty feeling', 'itchiness', 'sensitivity to light'],
    'medications' => ['Artificial tears', 'Antibiotic eye drops (if bacterial)', 'Antihistamine drops']
],
'Tonsillitis' => [
    'definition' => 'Infection and inflammation of the tonsils, often caused by viral or bacterial infections.',
    'symptoms' => ['white spots on tonsils', 'painful swallowing', 'swollen neck glands', 'fever', 'bad breath'],
    'medications' => ['Antibiotics (if bacterial)', 'Pain relievers (e.g., ibuprofen)', 'Warm saltwater gargles']
],
'Strep Throat' => [
    'definition' => 'A bacterial infection that can make your throat feel sore and scratchy.',
    'symptoms' => ['sore throat', 'red spots in mouth', 'swollen tonsils', 'fever', 'headache', 'pain when swallowing'],
    'medications' => ['Penicillin', 'Amoxicillin', 'Pain relievers']
],
'Laryngitis' => [
    'definition' => 'Inflammation of the larynx, causing voice changes and throat discomfort.',
    'symptoms' => ['hoarse voice', 'sore throat', 'dry throat', 'loss of voice', 'cough'],
    'medications' => ['Rest voice', 'Hydration', 'Lozenges']
],
'Sinus Infection (Sinusitis)' => [
    'definition' => 'An inflammation or swelling of the tissue lining the sinuses, often due to infection.',
    'symptoms' => ['facial pain', 'nasal congestion', 'thick nasal discharge', 'fever', 'headache'],
    'medications' => ['Decongestants', 'Nasal saline irrigation', 'Antibiotics (if bacterial)']
],

           // Other Conditions
'Headache' => [
    'definition' => 'Pain or discomfort in the head, scalp, or neck, often caused by tension, stress, or other factors.',
    'symptoms' => ['throbbing pain', 'tension', 'light sensitivity', 'nausea', 'pain in neck'],
    'medications' => ['NSAIDs (e.g., ibuprofen)', 'Acetaminophen', 'Caffeine-based pain relievers']
],
'Dizziness' => [
    'definition' => 'A sensation of lightheadedness, unsteadiness, or feeling faint.',
    'symptoms' => ['lightheadedness', 'feeling faint', 'loss of balance', 'nausea', 'ringing in ears'],
    'medications' => ['Meclizine', 'Hydration', 'Vitamin B12 (if deficient)']
],
'Fatigue' => [
    'definition' => 'A state of extreme tiredness resulting from mental or physical exertion or illness.',
    'symptoms' => ['persistent tiredness', 'lack of energy', 'sleep disturbances', 'difficulty concentrating'],
    'medications' => ['Vitamin supplements', 'Energy-boosting foods', 'Sleep aids (if insomnia)']
],
'Fever' => [
    'definition' => 'An increase in body temperature, often due to an infection or illness.',
    'symptoms' => ['high temperature', 'chills', 'sweating', 'headache', 'muscle aches', 'weakness'],
    'medications' => ['Paracetamol', 'Ibuprofen', 'Hydration with electrolytes']
],
'Stress' => [
    'definition' => 'A mental or emotional strain that can lead to various physical and psychological symptoms.',
    'symptoms' => ['tension', 'headaches', 'fatigue', 'difficulty sleeping', 'irritability', 'racing heart'],
    'medications' => ['Relaxation techniques', 'Herbal supplements (e.g., valerian)', 'SSRIs (if prescribed)']
],
'Weight Loss' => [
    'definition' => 'A reduction in body weight, which may be intentional or unintentional and could indicate an underlying health issue.',
    'symptoms' => ['increased hunger', 'fatigue', 'mood changes', 'muscle loss', 'dehydration'],
    'medications' => ['Nutritional supplements', 'Appetite stimulants', 'Treatment for underlying causes']
],
'Weakness' => [
    'definition' => 'A lack of physical strength or energy, often accompanied by fatigue.',
    'symptoms' => ['tiredness', 'muscle weakness', 'lack of energy', 'difficulty lifting things'],
    'medications' => ['Vitamin supplements', 'Iron supplements', 'Physical therapy']
],
'Nausea' => [
    'definition' => 'A sensation of unease and discomfort in the stomach, often leading to the urge to vomit.',
    'symptoms' => ['stomach discomfort', 'queasiness', 'dizziness', 'sweating', 'loss of appetite'],
    'medications' => ['Antiemetics (e.g., ondansetron)', 'Ginger supplements', 'Hydration']
],
'Hypertension' => [
    'definition' => 'A condition in which the force of the blood against the artery walls is too high, often leading to serious health issues.',
    'symptoms' => ['morning headaches', 'nosebleeds', 'blurred vision', 'chest pain', 'confusion'],
    'medications' => ['ACE inhibitors', 'Calcium channel blockers', 'Diuretics']
],
'Diabetes' => [
    'definition' => 'A chronic disease that occurs when the body is unable to properly process food for use as energy, leading to high blood sugar levels.',
    'symptoms' => ['constant thirst', 'frequent urination', 'weight loss', 'fatigue', 'blurred vision'],
    'medications' => ['Insulin therapy', 'Metformin', 'Blood sugar monitors']
],
'Arthritis' => [
    'definition' => 'A group of more than 100 diseases and conditions that cause pain, stiffness, and swelling in the joints.',
    'symptoms' => ['joint pain', 'stiffness', 'swelling', 'reduced range of motion', 'tenderness'],
    'medications' => ['NSAIDs', 'Corticosteroids', 'Physical therapy']
],
'Rheumatic Fever' => [
    'definition' => 'An inflammatory disease that can develop as a complication of untreated strep throat or scarlet fever.',
    'symptoms' => ['joint tenderness', 'painless lumps', 'muscle twitching', 'fever', 'chest pain'],
    'medications' => ['Antibiotics (e.g., penicillin)', 'Anti-inflammatory drugs', 'Corticosteroids']
],  
'Headache/Migraine' => [
    'definition' => 'A recurrent headache that can cause severe throbbing pain or a pulsing sensation, often accompanied by nausea and sensitivity to light.',
    'symptoms' => ['throbbing pain', 'nausea', 'light sensitivity', 'aura', 'dizziness'],
    'medications' => ['NSAIDs (e.g., ibuprofen)', 'Triptans (e.g., sumatriptan)', 'Anti-nausea drugs']
],
'Insomnia' => [
    'definition' => 'A sleep disorder characterized by difficulty falling asleep, staying asleep, or waking up too early.',
    'symptoms' => ['trouble sleeping', 'waking often', 'daytime tiredness', 'difficulty concentrating', 'irritability'],
    'medications' => ['Melatonin', 'Sedative-hypnotics (e.g., zolpidem)', 'Cognitive behavioral therapy for insomnia']
],
'Anxiety Disorder' => [
    'definition' => 'A mental health disorder characterized by feelings of worry, anxiety, or fear that interfere with daily activities.',
    'symptoms' => ['constant worry', 'fast heartbeat', 'sweating', 'restlessness', 'fatigue'],
    'medications' => ['SSRIs (e.g., sertraline)', 'Benzodiazepines (short-term use)', 'Buspirone']
],
'Depression' => [
    'definition' => 'A mood disorder that causes persistent feelings of sadness and loss of interest, affecting how one feels, thinks, and handles daily activities.',
    'symptoms' => ['loss of interest', 'low energy', 'trouble focusing', 'changes in appetite', 'sleep disturbances'],
    'medications' => ['SSRIs (e.g., fluoxetine)', 'SNRIs (e.g., venlafaxine)', 'Tricyclic antidepressants']
],

// Infectious Diseases
'Malaria' => [
    'definition' => 'A life-threatening disease caused by parasites transmitted to people through the bites of infected mosquitoes.',
    'symptoms' => ['fever and chills', 'sweating', 'headache', 'nausea', 'muscle pain'],
    'medications' => ['Chloroquine', 'Artemisinin-based combination therapies (ACTs)', 'Primaquine']
],
'Dengue Fever' => [
    'definition' => 'A mosquito-borne viral infection causing severe flu-like symptoms and, in some cases, developing into a potentially lethal complication.',
    'symptoms' => ['high fever', 'eye pain', 'rash after fever', 'joint pain', 'fatigue'],
    'medications' => ['Acetaminophen (avoid NSAIDs)', 'Oral rehydration salts', 'Supportive care']
],
'Measles' => [
    'definition' => 'A highly contagious viral disease characterized by a high fever, cough, and a distinctive red rash.',
    'symptoms' => ['red rash', 'runny nose', 'white spots in mouth', 'cough', 'fever'],
    'medications' => ['Vitamin A supplements', 'Fever reducers (e.g., acetaminophen)', 'Hydration']
],
'Scarlet Fever' => [
    'definition' => 'A bacterial infection that causes a bright red rash and is typically a complication of strep throat.',
    'symptoms' => ['red rash', 'strawberry tongue', 'sore throat', 'fever', 'headache'],
    'medications' => ['Penicillin', 'Amoxicillin', 'Antihistamines (for rash)']
],

           // Urinary and Reproductive Health
'Urinary Tract Infection (UTI)' => [
    'definition' => 'An infection in any part of the urinary system, including the kidneys, bladder, or urethra.',
    'symptoms' => ['burning during urination', 'cloudy urine', 'back pain', 'frequent urination', 'pelvic pain'],
    'medications' => ['Trimethoprim-sulfamethoxazole', 'Nitrofurantoin', 'Cranberry supplements (preventive)']
],
'Yeast Infection' => [
    'definition' => 'An infection caused by an overgrowth of yeast, typically Candida, in the vagina.',
    'symptoms' => ['itching', 'thick discharge', 'swelling', 'pain during intercourse', 'redness'],
    'medications' => ['Antifungal creams (e.g., clotrimazole)', 'Oral antifungals (e.g., fluconazole)', 'Probiotics (supportive)']
],

// Injuries
'Sprained Ankle' => [
    'definition' => 'An injury that occurs when ligaments in the ankle are stretched or torn, often due to twisting or rolling the ankle.',
    'symptoms' => ['swelling', 'bruising', 'pain when moving', 'instability', 'stiffness'],
    'medications' => ['Pain relievers (e.g., ibuprofen)', 'Cold compress', 'Supportive brace']
],
'Fracture' => [
    'definition' => 'A break in the bone that can result from trauma, overuse, or underlying health conditions.',
    'symptoms' => ['pain at injury site', 'unable to move limb', 'visible deformity', 'swelling', 'bruising'],
    'medications' => ['Pain relievers', 'Calcium and vitamin D (supportive)', 'Bisphosphonates (if related to osteoporosis)']
],

// Rare Conditions
'Guillain-Barré Syndrome' => [
    'definition' => 'A rare disorder in which the body\'s immune system attacks the peripheral nervous system, often leading to muscle weakness.',
    'symptoms' => ['weakness in legs', 'numbness', 'breathing issues', 'fatigue', 'difficulty walking'],
    'medications' => ['Plasmapheresis', 'Intravenous immunoglobulin (IVIG)', 'Supportive care']
],
'Marfan Syndrome' => [
    'definition' => 'A genetic disorder that affects the connective tissue, leading to features such as tall stature and long limbs.',
    'symptoms' => ['tall and thin', 'long fingers', 'heart palpitations', 'flexible joints', 'vision problems'],
    'medications' => ['Beta-blockers', 'Angiotensin receptor blockers (ARBs)', 'Surgical repair (if necessary)']
],
'Cystic Fibrosis' => [
    'definition' => 'A genetic disorder that affects the lungs and digestive system, characterized by the production of thick, sticky mucus.',
    'symptoms' => ['lung infections', 'weight gain trouble', 'coughing up mucus', 'salty skin', 'poor growth'],
    'medications' => ['Mucolytics (e.g., dornase alfa)', 'Inhaled antibiotics', 'Pancreatic enzyme supplements']
],
'Wilson’s Disease' => [
    'definition' => 'A genetic disorder that prevents the body from removing excess copper, leading to copper accumulation in tissues.',
    'symptoms' => ['yellow skin', 'tremors', 'mood changes', 'abdominal pain', 'swelling'],
    'medications' => ['Chelating agents (e.g., penicillamine)', 'Zinc acetate', 'Dietary adjustments (low copper)']
],
'Addison’s Disease' => [
    'definition' => 'A disorder that occurs when the adrenal glands do not produce enough hormones, leading to various symptoms.',
    'symptoms' => ['dark skin', 'fatigue', 'low blood sugar', 'muscle weakness', 'weight loss'],
    'medications' => ['Hydrocortisone', 'Fludrocortisone', 'Sodium supplements']
],
'Amyloidosis' => [
    'definition' => 'A rare disease that occurs when an abnormal protein, amyloid, builds up in organs and tissues.',
    'symptoms' => ['extreme fatigue', 'swollen legs', 'breathing trouble', 'numbness', 'heart problems'],
    'medications' => ['Chemotherapy (if AL amyloidosis)', 'Tafamidis (for ATTR amyloidosis)', 'Diuretics']
],
'Ehlers-Danlos Syndrome' => [
    'definition' => 'A group of connective tissue disorders characterized by hyper-flexible joints and skin that can be easily bruised.',
    'symptoms' => ['easily dislocated joints', 'stretchy skin', 'easy bruising', 'joint pain', 'fatigue'],
    'medications' => ['Pain relievers', 'Physiotherapy', 'Surgical repair (if necessary)']
],

 // Additional Famous Illnesses

// Neurological and Mental Health
'Multiple Sclerosis' => [
    'definition' => 'An autoimmune disease that affects the central nervous system, leading to communication problems between the brain and the body.',
    'symptoms' => ['numbness', 'tingling', 'weakness', 'vision problems', 'fatigue'],
    'medications' => ['Interferon beta', 'Glatiramer acetate', 'Corticosteroids']
],
'HIV/AIDS' => [
    'definition' => 'A virus that attacks the immune system, making the body vulnerable to infections and diseases.',
    'symptoms' => ['flu-like symptoms', 'weight loss', 'fever', 'night sweats', 'fatigue'],
    'medications' => ['Antiretroviral therapy (ART)', 'Tenofovir', 'Emtricitabine']
],
'Parkinson’s Disease' => [
    'definition' => 'A progressive nervous system disorder that affects movement, causing tremors and stiffness.',
    'symptoms' => ['tremors', 'slowed movement', 'muscle stiffness', 'balance problems', 'changes in speech'],
    'medications' => ['Levodopa', 'Dopamine agonists', 'MAO-B inhibitors']
],
'Chronic Obstructive Pulmonary Disease (COPD)' => [
    'definition' => 'A group of lung diseases that block airflow and make it difficult to breathe.',
    'symptoms' => ['shortness of breath', 'chronic cough', 'wheezing', 'chest tightness', 'frequent respiratory infections'],
    'medications' => ['Bronchodilators', 'Inhaled corticosteroids', 'Phosphodiesterase-4 inhibitors']
],
'Alzheimer’s Disease' => [
    'definition' => 'A progressive disease that destroys memory and other important mental functions.',
    'symptoms' => ['memory loss', 'confusion', 'difficulty with problem-solving', 'changes in mood', 'withdrawal from social activities'],
    'medications' => ['Donepezil', 'Rivastigmine', 'Memantine']
],
'Diabetes Mellitus' => [
    'definition' => 'A chronic condition that occurs when the body cannot effectively use insulin, leading to high blood sugar levels.',
    'symptoms' => ['increased thirst', 'frequent urination', 'extreme fatigue', 'blurred vision', 'slow healing of wounds'],
    'medications' => ['Insulin', 'Metformin', 'Sulfonylureas']
],

// Additional Common Illnesses
'Influenza (Flu)' => [
    'definition' => 'A contagious respiratory illness caused by influenza viruses, often leading to more severe symptoms than a cold.',
    'symptoms' => ['fever', 'chills', 'muscle aches', 'cough', 'fatigue'],
    'medications' => ['Antiviral drugs (e.g., oseltamivir)', 'Paracetamol', 'Ibuprofen']
],
'Stomach Flu (Gastroenteritis)' => [
    'definition' => 'An inflammation of the stomach and intestines, often caused by a viral infection.',
    'symptoms' => ['nausea', 'vomiting', 'diarrhea', 'stomach cramps', 'fever'],
    'medications' => ['Oral rehydration salts (ORS)', 'Probiotics', 'Antiemetics (e.g., ondansetron)']
],
'Gastroesophageal Reflux Disease (GERD)' => [
    'definition' => 'A chronic digestive condition where stomach acid or bile irritates the food pipe lining.',
    'symptoms' => ['heartburn', 'regurgitation', 'chest pain', 'difficulty swallowing', 'sore throat'],
    'medications' => ['Proton pump inhibitors (PPIs)', 'H2 blockers', 'Antacids']
],
'Acid Reflux' => [
    'definition' => 'A condition where stomach acid frequently flows back into the esophagus, causing irritation.',
    'symptoms' => ['burning sensation in chest', 'sour taste in mouth', 'difficulty swallowing', 'chronic cough', 'sore throat'],
    'medications' => ['Antacids', 'PPIs', 'H2 blockers']
],

'Allergies' => [
    'definition' => 'An overreaction of the immune system to substances that are typically harmless.',
    'symptoms' => ['sneezing', 'itchy eyes', 'runny nose', 'skin rash', 'hives'],
    'medications' => ['Antihistamines', 'Decongestants', 'Corticosteroids']
],
'Osteoarthritis' => [
    'definition' => 'A degenerative joint disease that results from the breakdown of joint cartilage and underlying bone.',
    'symptoms' => ['joint pain', 'stiffness', 'swelling', 'decreased range of motion', 'crunching sensation'],
    'medications' => ['NSAIDs', 'Corticosteroid injections', 'Topical analgesics']
],
'Rheumatoid Arthritis' => [
    'definition' => 'An autoimmune disorder that primarily affects joints, leading to inflammation and pain.',
    'symptoms' => ['joint pain', 'swelling', 'fatigue', 'fever', 'morning stiffness'],
    'medications' => ['Disease-modifying antirheumatic drugs (DMARDs)', 'Biologics (e.g., adalimumab)', 'NSAIDs']
],

'Psoriasis' => [
    'definition' => 'A chronic autoimmune condition that causes rapid skin cell production, leading to scaling and inflammation.',
    'symptoms' => ['red patches of skin', 'silvery scales', 'itching', 'dry skin', 'thickened nails'],
    'medications' => ['Topical corticosteroids', 'Vitamin D analogs', 'Biologic agents']
],
'Celiac Disease' => [
    'definition' => 'An autoimmune disorder where the ingestion of gluten leads to damage in the small intestine.',
    'symptoms' => ['diarrhea', 'bloating', 'weight loss', 'fatigue', 'anemia'],
    'medications' => ['Gluten-free diet', 'Vitamin and mineral supplements', 'Corticosteroids (severe cases)']
],
'Thyroid Disorders' => [
    'definition' => 'Conditions that affect the thyroid gland, including hypothyroidism and hyperthyroidism.',
    'symptoms' => ['fatigue', 'weight changes', 'mood changes', 'hair loss', 'sensitivity to temperature'],
    'medications' => ['Levothyroxine (for hypothyroidism)', 'Antithyroid drugs (for hyperthyroidism)', 'Beta-blockers (symptom relief)']
],
'Gout' => [
    'definition' => 'A form of arthritis characterized by sudden, severe attacks of pain, redness, and tenderness in joints.',
    'symptoms' => ['sudden pain', 'swelling', 'redness', 'heat in joints', 'limited range of motion'],
    'medications' => ['NSAIDs', 'Colchicine', 'Uric acid-lowering medications (e.g., allopurinol)']
],
'Migraine' => [
    'definition' => 'A neurological condition characterized by recurrent headaches that are moderate to severe.',
    'symptoms' => ['intense headache', 'nausea', 'vomiting', 'sensitivity to light and sound', 'visual disturbances'],
    'medications' => ['Triptans', 'NSAIDs', 'Anti-nausea drugs']
],

'Anxiety Disorders' => [
    'definition' => 'A group of mental health disorders characterized by significant feelings of anxiety and fear.',
    'symptoms' => ['excessive worry', 'restlessness', 'fatigue', 'difficulty concentrating', 'sleep disturbances'],
    'medications' => ['SSRIs', 'Benzodiazepines (short-term use)', 'Buspirone']
]
];


    
    
    
    

    // Normalize symptoms input
    $symptoms = strtolower($symptoms);
    $suggestedConditions = [];
    
    // Check if any symptoms match the defined conditions
    foreach ($conditions as $condition => $data) {
        foreach ($data['symptoms'] as $symptom) {
            if (strpos($symptoms, $symptom) !== false) {
                $suggestedConditions[] = [
                    'condition' => $condition,
                    'definition' => $data['definition'],
                    'medications' => $data['medications'] // Include medications
                ];
                break; // Break to avoid duplicate condition suggestions
            }
        }
    }
    
    // Return the suggested conditions or a default message
    return !empty($suggestedConditions)
        ? $suggestedConditions
        : [["condition" => "No significant symptoms detected. Consider consulting a healthcare professional.", "definition" => "", "medication" => []]];
    }
    
    // Process the form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $symptoms = trim($_POST['symptoms']);
        if (!empty($symptoms)) {
            $suggestions = analyzeSymptoms($symptoms);
        } else {
            $suggestions = [["condition" => "Please enter some symptoms to analyze.", "definition" => "", "medication" => []]];
        }
    }
    ?>
    

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Symptom Checker</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
      
      body { 
           
           margin: 0; 
           padding: 20px; 
           display: flex; 
           justify-content: center; 
           align-items: center; 
           flex-direction: column; 
       }
       body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
           font-size: 18px;
           background-image: url(image.jpeg);
           background-repeat: no-repeat;
           background-size: cover;
           background-attachment: fixed;
           color: black;
       }
        .checker-container {
            background-color: Lightblue; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); 
            max-width: 500px; 
            width: 88%;
        }
        h1 {
            text-align: center;
            color: white; 
        }
        .form-group {
            margin: 15px 0;
        }
        .form-group label {
            font-weight: bold;
        }
        .form-group textarea {
            width: 92%;
            height: 100px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #007bff;
        }
        .submit-button {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #28a745; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 16px; 
            transition: background 0.3s ease; 
        }
        .submit-button:hover {
            background-color: green; 
        }
        .suggestions {
            margin-top: 20px;
            padding: 15px;
            background-color: #d1ecf1;
            border-radius: 5px;
        }
        .definition {
            font-style: italic;
            margin-top: 5px;
            color: #555;
        }

        .btn {
    display: inline-block;
    background-color: #007bff; /* Button color */
    color: white; /* Text color */
    padding: 10px 20px; /* Padding around the text */
    border-radius: 5px; /* Rounded corners */
    text-decoration: none; /* Remove underline */
    transition: background-color 0.3s; /* Transition for hover effect */
}


header {
            background-color: green;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            height: 100px;
            border-radius: 10px;
        }

        footer {
            margin-top: 10px;
            text-align: center;
            padding: 10px 0;
            background-color: green;
            color: white;
            width: 100%;
            border-radius: 10px;
        }

        #backButton {
            display: inline-block; /* Allows padding and margin */
            background-color: #007bff; /* Blue background */
            color: white; /* White text */
            padding: 10px 20px; /* Padding around the text */
            font-size: 16px; /* Font size */
            border-radius: 5px; /* Rounded corners */
            text-decoration: none; /* Remove underline */
            transition: background-color 0.3s, transform 0.2s; /* Transition effects */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow effect */
            margin: 10px; /* Margin for spacing */
            width: 13.5%;
            margin-top: -20px;
            margin-left: -0.2px;
        }

        #backButton:hover {
            background-color: #0056b3; /* Darker blue on hover */
            transform: translateY(-2px); /* Slight lift effect */
        }

        #backButton:active {
            transform: translateY(0); /* Reset lift effect on click */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Reduce shadow on click */
        }

    </style>
</head>
<body>
    <div class="checker-container">
        <header>
        <h1>SYMPTOMS CHECKER</h1>
        </header>
       
        <form action="symptom_checker.php" method="POST">
    <div class="form-group">
        <label for="symptoms">Enter your symptoms (Be Specific, English only):</label>
        <textarea id="symptoms" name="symptoms" required><?php echo htmlspecialchars($symptoms); ?></textarea>
    </div>
    <button type="submit" class="submit-button">Analyze Symptoms</button>
</form>

<?php if (!empty($suggestions)): ?>
    <div class="suggestions">
        <footer>
            <p>Consult a qualified healthcare provider for a personalized treatment plan and evaluation of your symptoms, as they are not a substitute for professional medical advice!😊</p>
        </footer>

        <h2>Suggestions:</h2>
        <ul>
            <?php foreach ($suggestions as $suggestion): ?>
                <li>
                    <?php echo htmlspecialchars($suggestion['condition']); ?>
                    <div class="definition"><?php echo htmlspecialchars($suggestion['definition']); ?></div>
                    
                    <?php if (!empty($suggestion['medications'])): ?>
                        <div class="medications">
                            <strong>Suggested Medication:</strong> 
                            <?php 
                            // Check if medications is an array and join them into a string
                            if (is_array($suggestion['medications'])) {
                                echo htmlspecialchars(implode(', ', $suggestion['medications']));
                            } else {
                                echo htmlspecialchars($suggestion['medications']);
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

</body>
</html>

<?php
// Close database connection
mysqli_close($conn);
?>

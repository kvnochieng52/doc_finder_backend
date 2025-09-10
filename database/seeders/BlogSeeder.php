<?php

namespace Database\Seeders;

use App\Models\Blog;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $healthNews = [
            [
                'title' => 'Latest Breakthrough in Heart Disease Prevention',
                'excerpt' => 'Researchers discover new methods to prevent cardiovascular diseases through lifestyle modifications and early detection techniques.',
                'content' => '<p>Cardiovascular disease remains one of the leading causes of death worldwide, but recent research has unveiled promising new approaches to prevention. A comprehensive study involving over 50,000 participants has revealed that simple lifestyle modifications can reduce heart disease risk by up to 70%.</p>

<p>The study, published in the Journal of Preventive Cardiology, highlights the importance of:</p>
<ul>
<li>Regular physical activity - at least 150 minutes of moderate exercise per week</li>
<li>A Mediterranean-style diet rich in omega-3 fatty acids</li>
<li>Stress management techniques such as meditation and yoga</li>
<li>Quality sleep of 7-9 hours per night</li>
<li>Regular health screenings for early detection</li>
</ul>

<p>Dr. Sarah Johnson, lead researcher at the Institute of Cardiovascular Health, explains: "What we\'ve found is that prevention truly is better than cure. These lifestyle changes not only reduce the risk of heart disease but also improve overall quality of life."</p>

<p>The research also emphasizes the role of technology in monitoring heart health. Wearable devices can now track heart rate variability, sleep patterns, and activity levels, providing valuable data for both patients and healthcare providers.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=400&fit=crop',
                'author_name' => 'Dr. Sarah Johnson',
                'tags' => ['heart health', 'prevention', 'cardiovascular', 'lifestyle'],
                'status' => 'published',
                'is_featured' => true,
                'is_trending' => true,
                'views_count' => 1250,
                'published_at' => Carbon::now()->subDays(2)
            ],
            [
                'title' => 'Mental Health Awareness: Breaking the Stigma',
                'excerpt' => 'Understanding the importance of mental health care and how communities can support individuals struggling with mental health challenges.',
                'content' => '<p>Mental health awareness has gained significant momentum in recent years, yet stigma and misconceptions continue to prevent many individuals from seeking the help they need. This comprehensive guide explores the current state of mental health care and community support systems.</p>

<p>According to the World Health Organization, one in four people will be affected by mental or neurological disorders at some point in their lives. Despite this prevalence, mental health conditions are often misunderstood or dismissed.</p>

<h3>Common Mental Health Conditions:</h3>
<ul>
<li>Depression and anxiety disorders</li>
<li>Bipolar disorder</li>
<li>Post-traumatic stress disorder (PTSD)</li>
<li>Eating disorders</li>
<li>Substance abuse disorders</li>
</ul>

<p>Treatment options have evolved significantly, with evidence-based approaches including cognitive-behavioral therapy (CBT), medication when appropriate, and holistic treatments such as mindfulness-based interventions.</p>

<p>"The key to breaking stigma is education and open dialogue," says Dr. Michael Chen, a clinical psychologist. "When we talk about mental health with the same openness as physical health, we create an environment where people feel safe to seek help."</p>

<p>Community support plays a crucial role in mental health recovery. Support groups, peer counseling, and family education programs have shown remarkable success in helping individuals maintain their mental wellness.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=800&h=400&fit=crop',
                'author_name' => 'Dr. Michael Chen',
                'tags' => ['mental health', 'awareness', 'stigma', 'therapy'],
                'status' => 'published',
                'is_featured' => false,
                'is_trending' => true,
                'views_count' => 892,
                'published_at' => Carbon::now()->subDays(5)
            ],
            [
                'title' => 'Nutrition Science: The Power of Plant-Based Diets',
                'excerpt' => 'Latest research reveals how plant-based nutrition can improve health outcomes and reduce chronic disease risk.',
                'content' => '<p>Plant-based nutrition has moved from a niche dietary choice to mainstream medical recommendation, backed by extensive scientific research demonstrating its health benefits. Recent studies show that well-planned plant-based diets can significantly reduce the risk of chronic diseases.</p>

<p>A landmark study published in the American Journal of Clinical Nutrition followed 200,000 participants for 20 years, revealing that those following plant-based diets had:</p>
<ul>
<li>32% lower risk of cardiovascular disease</li>
<li>22% reduced risk of type 2 diabetes</li>
<li>15% lower cancer incidence rates</li>
<li>Improved digestive health and gut microbiome diversity</li>
</ul>

<h3>Key Components of Healthy Plant-Based Eating:</h3>
<ul>
<li>Whole grains and legumes for protein and fiber</li>
<li>Colorful fruits and vegetables for antioxidants</li>
<li>Nuts and seeds for healthy fats</li>
<li>Adequate B12 and vitamin D supplementation</li>
</ul>

<p>Nutritionist Dr. Emily Rodriguez emphasizes the importance of variety: "The magic of plant-based nutrition lies in diversity. Each plant food offers unique nutrients and compounds that work synergistically to promote health."</p>

<p>For those transitioning to plant-based eating, experts recommend a gradual approach, starting with "Meatless Mondays" or replacing one meal per day with plant-based options.</p>

<p>The environmental benefits are equally compelling, with plant-based diets requiring significantly less water and land resources while producing fewer greenhouse gas emissions.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&h=400&fit=crop',
                'author_name' => 'Dr. Emily Rodriguez',
                'tags' => ['nutrition', 'plant-based', 'diet', 'chronic disease'],
                'status' => 'published',
                'is_featured' => true,
                'is_trending' => false,
                'views_count' => 2108,
                'published_at' => Carbon::now()->subDays(7)
            ],
            [
                'title' => 'Sleep Health: The Foundation of Wellness',
                'excerpt' => 'Understanding the critical role of quality sleep in physical health, mental well-being, and cognitive function.',
                'content' => '<p>Sleep is no longer viewed as a luxury but as a fundamental pillar of health, equally important as nutrition and exercise. Recent neuroscience research has revealed the profound impact of sleep on every aspect of human health and performance.</p>

<p>During sleep, our bodies and brains undergo critical restoration processes:</p>
<ul>
<li>Memory consolidation and learning</li>
<li>Cellular repair and regeneration</li>
<li>Immune system strengthening</li>
<li>Toxin clearance from the brain</li>
<li>Hormone regulation</li>
</ul>

<p>Dr. Lisa Wang, a sleep medicine specialist, explains: "We\'ve discovered that sleep quality is just as important as sleep duration. Adults need 7-9 hours of quality sleep, but the depth and continuity of that sleep matters enormously."</p>

<h3>Common Sleep Disorders:</h3>
<ul>
<li>Sleep apnea affecting breathing during sleep</li>
<li>Insomnia and difficulty falling or staying asleep</li>
<li>Restless leg syndrome</li>
<li>Circadian rhythm disorders</li>
</ul>

<p>Sleep hygiene practices can dramatically improve sleep quality:</p>
<ul>
<li>Consistent sleep schedule, even on weekends</li>
<li>Creating a cool, dark, quiet sleep environment</li>
<li>Limiting screen time before bedtime</li>
<li>Regular exercise, but not close to bedtime</li>
<li>Avoiding caffeine and alcohol in the evening</li>
</ul>

<p>Technology is also playing a role in sleep health, with sleep tracking devices and apps helping individuals identify patterns and optimize their rest. However, experts caution against becoming too focused on the numbers rather than how you feel.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?w=800&h=400&fit=crop',
                'author_name' => 'Dr. Lisa Wang',
                'tags' => ['sleep', 'wellness', 'health', 'sleep hygiene'],
                'status' => 'published',
                'is_featured' => false,
                'is_trending' => true,
                'views_count' => 1567,
                'published_at' => Carbon::now()->subDays(3)
            ],
            [
                'title' => 'Exercise Medicine: Prescribing Movement for Health',
                'excerpt' => 'How healthcare providers are incorporating exercise prescriptions as a powerful tool for preventing and treating disease.',
                'content' => '<p>The concept of "exercise as medicine" is gaining traction in healthcare systems worldwide, with mounting evidence that physical activity can be as effective as medication for many health conditions. Healthcare providers are now prescribing specific exercise regimens to prevent and treat various diseases.</p>

<p>Research demonstrates that regular exercise can effectively treat or manage:</p>
<ul>
<li>Depression and anxiety (as effective as antidepressants in some cases)</li>
<li>Type 2 diabetes (improving insulin sensitivity)</li>
<li>Hypertension (reducing blood pressure)</li>
<li>Osteoporosis (strengthening bones)</li>
<li>Chronic pain conditions</li>
</ul>

<p>"We\'re seeing a paradigm shift where exercise is no longer just recommended but prescribed with specific dosages, frequency, and intensity," says Dr. Robert Kim, a sports medicine physician.</p>

<h3>Exercise Prescription Guidelines:</h3>
<ul>
<li><strong>Aerobic Exercise:</strong> 150 minutes moderate or 75 minutes vigorous per week</li>
<li><strong>Strength Training:</strong> 2-3 sessions per week targeting all major muscle groups</li>
<li><strong>Flexibility:</strong> Daily stretching and mobility work</li>
<li><strong>Balance:</strong> Especially important for older adults</li>
</ul>

<p>The key to successful exercise prescription is individualization. Healthcare providers now consider factors such as age, fitness level, medical history, and personal preferences when creating exercise plans.</p>

<p>Exercise physiologists work alongside doctors to ensure patients receive appropriate guidance and support. This collaborative approach has led to better adherence rates and improved health outcomes.</p>

<p>For those new to exercise, the message is clear: start small and build gradually. Even 10 minutes of daily movement can provide significant health benefits.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=400&fit=crop',
                'author_name' => 'Dr. Robert Kim',
                'tags' => ['exercise', 'medicine', 'fitness', 'prescription'],
                'status' => 'published',
                'is_featured' => false,
                'is_trending' => false,
                'views_count' => 734,
                'published_at' => Carbon::now()->subDays(10)
            ],
            [
                'title' => 'Digital Health Revolution: Telemedicine and Beyond',
                'excerpt' => 'Exploring how technology is transforming healthcare delivery and improving patient access to medical care.',
                'content' => '<p>The digital health revolution has accelerated dramatically, fundamentally changing how healthcare is delivered and accessed. From telemedicine consultations to AI-powered diagnostics, technology is making healthcare more accessible, efficient, and personalized.</p>

<p>The COVID-19 pandemic served as a catalyst for digital health adoption, with telehealth visits increasing by over 3,000% in some regions. This rapid adoption revealed both the potential and challenges of digital healthcare delivery.</p>

<h3>Key Digital Health Technologies:</h3>
<ul>
<li><strong>Telemedicine:</strong> Virtual consultations and remote monitoring</li>
<li><strong>Wearable Devices:</strong> Continuous health monitoring and data collection</li>
<li><strong>AI Diagnostics:</strong> Machine learning for medical imaging and diagnosis</li>
<li><strong>Electronic Health Records:</strong> Improved data sharing and care coordination</li>
<li><strong>Mobile Health Apps:</strong> Patient engagement and self-management tools</li>
</ul>

<p>Dr. Amanda Foster, a digital health expert, notes: "We\'re witnessing a democratization of healthcare. Patients now have access to medical expertise regardless of their geographic location, and continuous monitoring provides unprecedented insights into health patterns."</p>

<p>However, challenges remain, including:</p>
<ul>
<li>Digital divide and access issues</li>
<li>Privacy and security concerns</li>
<li>Integration with existing healthcare systems</li>
<li>Regulatory compliance and quality assurance</li>
</ul>

<p>The future of digital health looks promising, with emerging technologies like virtual reality for therapy, blockchain for secure health records, and precision medicine based on genetic data.</p>

<p>As these technologies continue to evolve, the focus remains on improving patient outcomes while maintaining the human touch that is essential to healthcare.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=800&h=400&fit=crop',
                'author_name' => 'Dr. Amanda Foster',
                'tags' => ['digital health', 'telemedicine', 'technology', 'healthcare'],
                'status' => 'published',
                'is_featured' => true,
                'is_trending' => false,
                'views_count' => 1923,
                'published_at' => Carbon::now()->subDays(4)
            ],
            [
                'title' => 'Women\'s Health: Addressing the Care Gap',
                'excerpt' => 'Examining disparities in women\'s healthcare and the movement toward more inclusive and comprehensive care.',
                'content' => '<p>Women\'s health has historically been underrepresented in medical research and clinical practice, leading to significant care gaps and health disparities. Recent initiatives are working to address these inequalities and improve health outcomes for women across all life stages.</p>

<p>Key areas where women face unique health challenges include:</p>
<ul>
<li>Reproductive health and family planning</li>
<li>Hormonal changes throughout life stages</li>
<li>Autoimmune conditions (affecting women 3-4 times more than men)</li>
<li>Mental health during pregnancy and menopause</li>
<li>Cardiovascular disease presentation and treatment</li>
</ul>

<p>"For too long, women\'s health concerns were dismissed or attributed to hormones without proper investigation," explains Dr. Patricia Martinez, an obstetrician-gynecologist. "We\'re now seeing a shift toward evidence-based, personalized care that recognizes women\'s unique health needs."</p>

<h3>Recent Advances in Women\'s Health:</h3>
<ul>
<li>Improved screening guidelines for breast and cervical cancer</li>
<li>Better understanding of heart disease symptoms in women</li>
<li>Advances in fertility preservation and reproductive technologies</li>
<li>Menopause management beyond hormone replacement therapy</li>
<li>Recognition of postpartum depression and anxiety</li>
</ul>

<p>The movement toward personalized medicine is particularly relevant for women\'s health, as genetic factors, hormonal fluctuations, and life experiences create unique health profiles that require individualized approaches.</p>

<p>Healthcare providers are also being trained to recognize implicit bias and provide culturally sensitive care that addresses the diverse needs of women from different backgrounds and life circumstances.</p>

<p>Education and advocacy remain crucial, with women being encouraged to take active roles in their healthcare decisions and to seek second opinions when their concerns are not adequately addressed.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=800&h=400&fit=crop',
                'author_name' => 'Dr. Patricia Martinez',
                'tags' => ['womens health', 'healthcare', 'gender equity', 'reproductive health'],
                'status' => 'published',
                'is_featured' => false,
                'is_trending' => true,
                'views_count' => 1445,
                'published_at' => Carbon::now()->subDays(6)
            ],
            [
                'title' => 'Aging Well: Healthy Longevity and Active Aging',
                'excerpt' => 'Research-backed strategies for maintaining health, independence, and quality of life as we age.',
                'content' => '<p>As global life expectancy continues to increase, the focus has shifted from simply living longer to aging well. Research in gerontology and geriatric medicine is revealing strategies that can help maintain health, cognitive function, and independence throughout the aging process.</p>

<p>The concept of "successful aging" encompasses:</p>
<ul>
<li>Physical health and functional capacity</li>
<li>Cognitive health and mental acuity</li>
<li>Social engagement and meaningful relationships</li>
<li>Emotional well-being and life satisfaction</li>
<li>Financial security and healthcare access</li>
</ul>

<p>Dr. George Thompson, a geriatrician, emphasizes: "Aging is not a disease but a natural process. With proper planning and lifestyle choices, we can significantly influence how we age and maintain quality of life well into our later years."</p>

<h3>Evidence-Based Strategies for Healthy Aging:</h3>
<ul>
<li><strong>Regular Physical Activity:</strong> Maintaining muscle mass, bone density, and cardiovascular health</li>
<li><strong>Cognitive Stimulation:</strong> Learning new skills, reading, and social interaction</li>
<li><strong>Preventive Healthcare:</strong> Regular screenings and vaccinations</li>
<li><strong>Nutrition:</strong> Adequate protein, vitamin D, and hydration</li>
<li><strong>Social Connection:</strong> Maintaining relationships and community involvement</li>
</ul>

<p>Common age-related health concerns include:</p>
<ul>
<li>Sarcopenia (muscle loss) and frailty</li>
<li>Cognitive decline and dementia prevention</li>
<li>Falls prevention and bone health</li>
<li>Chronic disease management</li>
<li>Medication management and polypharmacy</li>
</ul>

<p>Technology is playing an increasing role in supporting healthy aging, from medication reminder apps to home monitoring systems that can detect changes in daily routines that might indicate health concerns.</p>

<p>The aging population is also driving innovation in healthcare delivery, with more focus on home-based care, geriatric specialists, and age-friendly healthcare environments.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1581833971358-2c8b550f87b3?w=800&h=400&fit=crop',
                'author_name' => 'Dr. George Thompson',
                'tags' => ['aging', 'longevity', 'geriatrics', 'active aging'],
                'status' => 'published',
                'is_featured' => false,
                'is_trending' => false,
                'views_count' => 967,
                'published_at' => Carbon::now()->subDays(8)
            ]
        ];

        foreach ($healthNews as $article) {
            Blog::create($article);
        }
    }
}
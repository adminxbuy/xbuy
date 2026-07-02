<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<h1>Privacy Policy</h1><p>Welcome to the Privacy Policy of X-Buy.in (accessible via www.x-buy.in). Your privacy and data security are core priorities of our operations. This policy governs all data collection, storage, transfer, and processing practices utilized on our platform.</p><h2>1. Information We Collect</h2><p>We collect several types of information for various purposes to provide and improve our service to you. This includes: Personal Data (Email address, First name and last name, Phone number, Address, State, Province, ZIP/Postal code, City, Cookies and Usage Data); and Device/Usage Data (IP addresses, browser type, pages visited, time and date of visit, and unique device identifiers).</p><h2>2. How We Use and Process Your Information</h2><p>X-Buy.in uses the collected data for diverse operational workflows: to maintain service uptime, notify users of order transitions, allow interactive user collaboration, process payments via escrow channels, verify KYC details to prevent fraud, perform platform usage analytics, and deliver critical system notices. We do not sell or trade your personal data to third parties.</p><h2>3. Data Retentions and Deletion Policies</h2><p>We retain your personal data only for as long as necessary for the purposes set out in this Privacy Policy. We will retain and use your information to the extent necessary to comply with legal obligations, resolve dispute states, and enforce our binding policies. Users may request account deletion under local privacy statutes, subject to active transactional holds.</p><h2>4. Cookie Consent and Tracking Technologies</h2><p>We use cookies and similar tracking technologies to track the activity on our service and hold certain information. Cookies are files with small amount of data which may include an anonymous unique identifier. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.</p><h2>5. Third-Party Integrations and Escrow Service Providers</h2><p>Our platform routes payments through escrow providers (e.g. Razorpay) and coordinates shipping through logistic vendors (e.g. Shiprocket). These third parties have access to your Personal Data only to perform these tasks on our behalf and are obligated not to disclose or use it for any other purpose.</p>',
                'meta_title' => 'Privacy Policy | X-Buy.in',
                'meta_description' => 'Read X-Buy\'s privacy policy detailing how we protect and manage your personal data.',
                'is_active' => true,
                'is_protected' => true,
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => '<h1>Terms of Service</h1><p>Welcome to X-Buy.in. These Terms of Service ("Terms") constitute a legally binding agreement made between you, whether personally or on behalf of an entity ("you") and X-Buy.in ("we", "us", or "our"), concerning your access to and use of our website and applications.</p><h2>1. Registration and Account Integrity</h2><p>To list hardware or execute purchases, you must register for an account. You represent and warrant that all registration information you submit is accurate, current, and complete. You are responsible for maintaining account confidentiality and security keys. We reserve the right to suspend or ban accounts that breach registration declarations.</p><h2>2. Buying and Selling Rules & Covenants</h2><p>Sellers agree that product descriptions, model names, grades, and serial numbers must represent the actual condition of the computer components. Stock photos are prohibited. Buyers agree to pay for verified items through the designated gateway checkout path. All transactions must occur through our secure escrow holding system; bypassing this system violates the Terms and nullifies buyer/seller protections.</p><h2>3. Escrow System and Fund Management</h2><p>X-Buy holds buyer funds in a secure escrow account once payment is completed. Funds remain in escrow until the category-specific testing window expires without dispute submission, or the buyer explicitly confirms delivery. System commissions are automatically deducted from the seller\'s final payout at settlement.</p><h2>4. Limitation of Liability and Warranties</h2><p>X-Buy.in is a peer-to-peer marketplace. We do not own, inspect, or warrant physical components listed on the site. All components are sold "as is". In no event shall X-Buy.in, its directors, or employees be liable for direct, indirect, incidental, or consequential damages resulting from transaction failures, component defects, or system offline states.</p><h2>5. Dispute Jurisdiction and Governing Law</h2><p>These Terms and your use of the website are governed by and construed in accordance with the laws of India. Any legal action arising out of these Terms shall be filed exclusively in the competent courts located in Bangalore, Karnataka.</p>',
                'meta_title' => 'Terms of Service | X-Buy.in',
                'meta_description' => 'Read our terms and conditions for buying and selling PC parts securely on X-Buy.',
                'is_active' => true,
                'is_protected' => true,
            ],
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => '<h1>About Us</h1><p>X-Buy.in is India\'s safest PC parts marketplace. We are dedicated to providing a secure escrow platform for buying and selling second-hand and refurbished computer hardware.</p><p>Our mission is to build trust in the pre-owned PC hardware community through verified listings, testing windows, and secure holding of funds.</p>',
                'meta_title' => 'About Us | X-Buy.in',
                'meta_description' => 'Learn more about X-Buy, India\'s safest escrow marketplace for PC components.',
                'is_active' => true,
                'is_protected' => true,
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => '<h1>Contact Us</h1><p>Have questions, concerns, or feedback? Get in touch with the X-Buy support team.</p><p>Email: support@x-buy.in</p><p>We typically respond within 24-48 business hours.</p>',
                'meta_title' => 'Contact Us | X-Buy.in',
                'meta_description' => 'Contact X-Buy customer support for any questions regarding escrow or disputes.',
                'is_active' => true,
                'is_protected' => true,
            ],
            [
                'title' => 'Escrow Policy',
                'slug' => 'escrow-policy',
                'content' => '<h1>Escrow Policy</h1><p>This Escrow Policy details the security mechanisms protecting all transactional funds on X-Buy.in. By purchasing or listing products on our marketplace, you agree to these payment holding rules.</p><h2>1. Holding Period and Fund Allocations</h2><p>Upon a buyer\'s successful checkout, the entire order amount (product price + shipping charges) is immediately captured and held in our secure escrow holding system. The seller is notified to pack and ship the item. The seller has no access to the funds during this holding period.</p><h2>2. Verification and testing window</h2><p>The escrow holding period extends throughout the shipping process and the subsequent testing window (2-3 days based on product categories). The testing window begins when delivery is logged. During this period, the buyer is expected to verify and benchmark the component.</p><h2>3. Conditions for Fund Release</h2><p>Escrow funds are released to the seller\'s payout balance under the following conditions: (a) the buyer confirms the item works by clicking "Confirm Delivery" or "Complete Order" in their panel; (b) the testing window expires without any disputes being opened by the buyer. If a dispute is raised, escrow is locked until resolution.</p><h2>4. Commission Deductions</h2><p>Our platform commission is calculated as a percentage of the product listing price. This fee is automatically deducted from the escrow hold prior to finalizing the payout. The remaining balance (price minus commission) is dispatched to the seller\'s bank account or UPI ID.</p>',
                'meta_title' => 'Escrow Policy | X-Buy.in',
                'meta_description' => 'Understand how X-Buy\'s escrow system protects your money and components.',
                'is_active' => true,
                'is_protected' => true,
            ],
            [
                'title' => 'Seller Guidelines',
                'slug' => 'seller-guidelines',
                'content' => '<h1>Seller Guidelines</h1><p>To maintain high platform standards and ensure positive feedback ratings, all registered sellers must adhere to these operational and quality standards.</p><h2>1. Accurate Component Listings</h2><p>Sellers must specify correct category tags, brand mappings, model numbers, and grading criteria. All defects, cosmetic wear, and missing accessories must be disclosed in the description. The product serial number must be entered accurately during the listing creation flow.</p><h2>2. Shipping and Fulfillment Timelines</h2><p>Upon order confirmation, the seller must print shipping labels and ship the item within 48 hours. Sellers must utilize approved logistics services. Failure to ship within 72 hours may lead to automatic order cancellation and refund of escrow funds to the buyer.</p><h2>3. Quality and Packaging Control</h2><p>Sellers are responsible for secure packaging. Static-sensitive components (GPUs, CPUs, RAM) must be wrapped in anti-static bags and shipped in padded boxes to prevent transit damage. Sellers are encouraged to film a video of the component\'s working state and the packaging process as dispute evidence.</p><h2>4. Penalty System</h2><p>Sellers with high dispute rates, late shipping histories, or those who attempt to bypass escrow payments will face account suspension, rating downgrades, or permanent bans from the X-Buy marketplace.</p>',
                'meta_title' => 'Seller Guidelines | X-Buy.in',
                'meta_description' => 'Rules and guidelines for selling computer hardware on X-Buy.',
                'is_active' => true,
                'is_protected' => true,
            ],
            [
                'title' => 'Buyer Protection',
                'slug' => 'buyer-protection',
                'content' => '<h1>Buyer Protection</h1><p>Our Buyer Protection policy is designed to ensure you get exactly what you ordered or receive a full refund. Your purchases are covered by our secure escrow workflow.</p><h2>1. Coverage Thresholds</h2><p>Buyers are protected if: (a) the item does not arrive; (b) the item is damaged during transit; (c) the item received is functionally defective; (d) the item does not match the description (wrong model, different specifications, or incorrect grade).</p><h2>2. Raising a Dispute</h2><p>To claim Buyer Protection, you must open a dispute in your buyer dashboard before the category testing window expires. You will need to provide detailed evidence, including photos or videos showing the issue. Once a dispute is opened, the escrow release is immediately halted.</p><h2>3. Resolution and Verification Process</h2><p>Our support team reviews the evidence provided by both parties. If the item is verified as defective or incorrect, the buyer will return the component to the seller, and a full refund will be processed back to the original payment method upon return confirmation.</p>',
                'meta_title' => 'Buyer Protection | X-Buy.in',
                'meta_description' => 'Learn how buyers are protected from scams and faulty parts.',
                'is_active' => true,
                'is_protected' => true,
            ],
            [
                'title' => 'Refund Policy',
                'slug' => 'refund-policy',
                'content' => '<h1>Refund Policy</h1><p>This Refund Policy explains when and how refunds are issued for transactions on the X-Buy.in platform.</p><h2>1. Eligible Refund Scenarios</h2><p>Refunds are initiated under these conditions: (a) order is cancelled by the seller before shipment; (b) the seller fails to ship the package within the designated 72-hour window; (c) a dispute is resolved in the buyer\'s favor, and the return of the component is confirmed by tracking.</p><h2>2. Refund Processing Timelines</h2><p>Approved refunds are processed back to the original payment source (Credit/Debit Card, Netbanking, UPI, or Wallet). Refunds typically reflect in the buyer\'s account within 5-7 business days, depending on bank processing cycles.</p><h2>3. Non-refundable Fees</h2><p>Shipping charges are non-refundable if the return is due to buyer change-of-mind. If the dispute is due to seller error or defective components, the seller pays return logistics costs, and the buyer receives a full refund including original shipping fees.</p>',
                'meta_title' => 'Refund Policy | X-Buy.in',
                'meta_description' => 'Details on our dispute resolution and refund timelines.',
                'is_active' => true,
                'is_protected' => true,
            ],
            [
                'title' => 'FAQs',
                'slug' => 'faqs',
                'content' => '<h1>Frequently Asked Questions (FAQ)</h1><h2>1. How does the escrow process work?</h2><p>Escrow is a financial agreement where X-Buy holds the buyer\'s payment until the product is delivered and tested. Once the buyer verifies the part or the testing window expires, the payment is securely transferred to the seller.</p><h2>2. Can I cancel my order?</h2><p>Buyers can cancel their order at any time before the seller confirms and ships the package. Once shipped, the order cannot be cancelled, and returns can only be processed through the dispute system if the item is defective.</p><h2>3. What is the testing window duration?</h2><p>The testing window duration depends on the category of the component: GPUs and Motherboards have a 3-day window; CPUs, RAM, and Storage have a 2-day window. Cabinets and Fans have a 1-day window.</p><h2>4. Who pays for return shipping?</h2><p>If an item is defective or does not match the description, the seller must cover the return shipping costs. If the return is due to buyer remorse, the buyer is responsible for return courier fees.</p>',
                'meta_title' => 'FAQs | X-Buy.in',
                'meta_description' => 'Frequently asked questions about X-Buy escrow, shipping, and testing.',
                'is_active' => true,
                'is_protected' => true,
            ],
            [
                'title' => 'Careers',
                'slug' => 'careers',
                'content' => '<h1>Careers</h1><p>Join the team behind India\'s safest PC parts marketplace. We are always looking for passionate engineers, customer advocates, and marketing specialists.</p>',
                'meta_title' => 'Careers | X-Buy.in',
                'meta_description' => 'Join the X-Buy team and build the future of pre-owned hardware e-commerce.',
                'is_active' => true,
                'is_protected' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }
}

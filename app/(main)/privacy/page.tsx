import Link from "next/link";

export default function PrivacyPolicyPage() {
  return (
    <div className="min-h-screen bg-[#0a0a0f] py-12 px-6 md:px-16 lg:px-24 xl:px-32">
      <div className="max-w-4xl mx-auto">
        <h1 className="text-3xl font-bold text-white mb-8">Privacy Policy</h1>

        <div className="bg-white/5 rounded-lg p-8 text-gray-300">
          <p className="mb-6">
            At Telfaza LIVE, we are committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our services.
          </p>

          <div className="space-y-6">
            <section>
              <h2 className="text-xl font-semibold text-white mb-3">1. Information We Collect</h2>
              <p className="mb-3">
                We may collect information about you in a variety of ways. The information we may collect includes:
              </p>
              <ul className="list-disc list-inside space-y-2 ml-4">
                <li>Personal Data: Personally identifiable information, such as your name, email address, and other information that you voluntarily give to us.</li>
                <li>Derivative Data: Information our servers automatically collect when you access the site, such as your IP address, browser type, and operating system.</li>
                <li>Mobile Device Data: Device information, such as your mobile device ID, model, and manufacturer, and information about the location of your device.</li>
              </ul>
            </section>

            <section>
              <h2 className="text-xl font-semibold text-white mb-3">2. Use of Your Information</h2>
              <p className="mb-3">
                Having accurate information about you permits us to provide you with a smooth, efficient, and customized experience. Specifically, we may use information collected about you via the site to:
              </p>
              <ul className="list-disc list-inside space-y-2 ml-4">
                <li>Create and manage your account.</li>
                <li>Deliver targeted advertising, coupons, newsletters, and other information regarding promotions and the site to you.</li>
                <li>Improve our website and services.</li>
                <li>Respond to customer service requests.</li>
              </ul>
            </section>

            <section>
              <h2 className="text-xl font-semibold text-white mb-3">3. Disclosure of Your Information</h2>
              <p className="mb-3">
                We may share information we have collected about you in certain situations. Your information may be disclosed as follows:
              </p>
              <ul className="list-disc list-inside space-y-2 ml-4">
                <li>By Law or to Protect Rights: If we believe the release of information about you is necessary to respond to legal process, to investigate or remedy potential violations of our policies, or to protect the rights, property, and safety of others.</li>
                <li>Business Transfers: We may share or transfer your information in connection with, or during negotiations of, any merger, sale of company assets, financing, or acquisition of all or a portion of our business to another company.</li>
              </ul>
            </section>

            <section>
              <h2 className="text-xl font-semibold text-white mb-3">4. Security of Your Information</h2>
              <p className="mb-3">
                We use administrative, technical, and physical security measures to help protect your personal information. While we have taken reasonable steps to secure the personal information you provide to us, please be aware that despite our efforts, no security measures are perfect or impenetrable.
              </p>
            </section>

            <section>
              <h2 className="text-xl font-semibold text-white mb-3">5. Policy for Children</h2>
              <p className="mb-3">
                We do not knowingly solicit information from or market to children under the age of 13. If we learn that we have collected personal information from a child under age 13 without verification of parental consent, we will delete that information as quickly as possible.
              </p>
            </section>

            <section>
              <h2 className="text-xl font-semibold text-white mb-3">6. Changes to This Privacy Policy</h2>
              <p className="mb-3">
                We may update this Privacy Policy from time to time in order to reflect, for example, changes to our practices or for other operational, legal, or regulatory reasons.
              </p>
            </section>

            <section>
              <h2 className="text-xl font-semibold text-white mb-3">7. Contact Us</h2>
              <p>
                If you have questions or comments about this Privacy Policy, please contact us at: support@telfazalive.com
              </p>
            </section>
          </div>

          <div className="mt-8 pt-6 border-t border-gray-600">
            <Link href="/" className="text-orange-500 hover:text-orange-400 transition-colors">
              ← Back to Home
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
import Link from "next/link";

export default function CookiePolicyPage() {
  return (
    <div className="min-h-screen bg-[#0a0a0f] py-12 px-6 md:px-16 lg:px-24 xl:px-32">
      <div className="max-w-4xl mx-auto">
        <h1 className="text-3xl font-bold text-white mb-8">Cookie Policy</h1>

        <div className="bg-white/5 rounded-lg p-8 text-gray-300">
          <p className="mb-6">
            This Cookie Policy explains how Telfaza LIVE ("we", "us", or "our") uses cookies and similar technologies to recognize you when you visit our website. It explains what these technologies are and why we use them, as well as your rights to control our use of them.
          </p>

          <div className="space-y-6">
            <section>
              <h2 className="text-xl font-semibold text-white mb-3">1. What are cookies?</h2>
              <p className="mb-3">
                Cookies are small data files that are placed on your computer or mobile device when you visit a website. Cookies are widely used by website owners in order to make their websites work, or to work more efficiently, as well as to provide reporting information.
              </p>
              <p className="mb-3">
                Cookies set by the website owner (in this case, Telfaza LIVE) are called "first party cookies". Cookies set by parties other than the website owner are called "third party cookies". Third party cookies enable third party features or functionality to be provided on or through the website (e.g. like advertising, interactive content and analytics).
              </p>
            </section>

            <section>
              <h2 className="text-xl font-semibold text-white mb-3">2. Why do we use cookies?</h2>
              <p className="mb-3">
                We use first and third party cookies for several reasons. Some cookies are required for technical reasons in order for our website to operate, and we refer to these as "essential" or "strictly necessary" cookies. Other cookies also enable us to track and target the interests of our users to enhance the experience on our website.
              </p>
              <p className="mb-3">Our cookies are used for the following purposes:</p>
              <ul className="list-disc list-inside space-y-2 ml-4">
                <li>Essential website operations</li>
                <li>Authentication and security</li>
                <li>Analytics and performance</li>
                <li>User experience personalization</li>
                <li>Advertising and marketing</li>
              </ul>
            </section>

            <section>
              <h2 className="text-xl font-semibold text-white mb-3">3. What types of cookies do we use?</h2>
              <p className="mb-3">We use the following types of cookies on our website:</p>
              <ul className="list-disc list-inside space-y-2 ml-4">
                <li>
                  <strong>Essential Cookies:</strong> These cookies are strictly necessary to provide you with services available through our website and to use some of its features.
                </li>
                <li>
                  <strong>Performance and Functionality Cookies:</strong> These cookies are used to enhance the performance and functionality of our website but are non-essential to their use.
                </li>
                <li>
                  <strong>Analytics and Customization Cookies:</strong> These cookies collect information that is used either in aggregate form to help us understand how our website is being used or how effective our marketing campaigns are.
                </li>
                <li>
                  <strong>Advertising Cookies:</strong> These cookies are used to make advertising messages more relevant to you. They perform functions like preventing the same ad from continuously reappearing and ensuring that ads are properly displayed.
                </li>
              </ul>
            </section>

            <section>
              <h2 className="text-xl font-semibold text-white mb-3">4. How can you control cookies?</h2>
              <p className="mb-3">
                You have the right to decide whether to accept or reject cookies. You can exercise your cookie preferences by clicking on the appropriate opt-out links provided in the cookie banner that appears when you first visit our website.
              </p>
              <p className="mb-3">
                You can also set or amend your web browser controls to accept or refuse cookies. If you choose to reject cookies, you may still use our website though your access to some functionality and areas of our website may be restricted.
              </p>
            </section>

            <section>
              <h2 className="text-xl font-semibold text-white mb-3">5. Updates to this Cookie Policy</h2>
              <p className="mb-3">
                We may update this Cookie Policy from time to time in order to reflect, for example, changes to the cookies we use or for other operational, legal or regulatory reasons. Please therefore re-visit this Cookie Policy regularly to stay informed about our use of cookies and related technologies.
              </p>
            </section>

            <section>
              <h2 className="text-xl font-semibold text-white mb-3">6. Contact Us</h2>
              <p>
                If you have any questions about our use of cookies or other technologies, please email us at support@telfazalive.com.
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
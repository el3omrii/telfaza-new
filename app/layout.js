import './globals.css';
import TopLoader from '../components/TopLoader';
import PageTransition from '../components/PageTransition';

export const metadata = {
  title: 'StreamTV - Watch TV Channels Online',
  description: 'Stream thousands of TV channels from around the world. Browse by category, country, or popularity.'
};

export default function RootLayout({ children }) {
  return (
    <html lang="en">
      <body>
        <TopLoader />
        <PageTransition>
          {children}
        </PageTransition>
      </body>
    </html>
  );
}

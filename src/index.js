import { createRoot } from '@wordpress/element';
import App from './App';
import './style.scss';

/**
 * Initialize the React application once the DOM is ready.
 */
document.addEventListener('DOMContentLoaded', () => {
    const rootElement = document.getElementById('artemisia-forms-app');
    
    if (rootElement) {
        const root = createRoot(rootElement);
        root.render(<App />);
    }
});

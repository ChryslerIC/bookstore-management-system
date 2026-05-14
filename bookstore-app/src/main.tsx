import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './index.css'
import App from './App.tsx'

// Use document.getElementById('root') to get the DOM element with id="root" from index.html
// The ! (non-null assertion) tells TypeScript this element definitely exists
// Use createRoot() from React 18 to create a root for rendering React components
// Use .render() method to render the React component tree into the DOM
createRoot(document.getElementById('root')!).render(
  // Use StrictMode component to enable React development mode checks
  // StrictMode highlights potential problems in the application (not rendered in production)
  <StrictMode>
    {/* Render the App component which contains all routes and pages */}
    <App />
  </StrictMode>,
)

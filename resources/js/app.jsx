import React from 'react';
import { createRoot } from 'react-dom/client';
import Router from './Router';
import '../css/app.css';

const root = createRoot(document.getElementById('root'));
root.render(
  <React.StrictMode>
    <Router />
  </React.StrictMode>
);

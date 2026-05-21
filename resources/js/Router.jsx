import React from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import HomePage from './pages/HomePage';
import AdminRedirect from './pages/AdminRedirect';

export default function Router() {
  const basePath = import.meta.env.VITE_APP_BASE ?? '';

  return (
    <BrowserRouter basename={basePath}>
      <Routes>
        <Route path="/" element={<HomePage />} />
        <Route path="/admin" element={<AdminRedirect />} />
      </Routes>
    </BrowserRouter>
  );
}

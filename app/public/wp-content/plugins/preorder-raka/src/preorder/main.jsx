import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import PreOrderApp from './PreOrderApp';
import AdminAddPreorder from '../admin/AdminAddPreorder';

document.addEventListener('DOMContentLoaded', () => {
	// Front-end single app mount
	const container = document.getElementById('preorder-raka-single-app');
	if (container) {
		const postId = container.dataset.postId ? parseInt(container.dataset.postId, 10) : null;
		const root = createRoot(container);
		root.render(
			<React.StrictMode>
				<PreOrderApp postId={postId} />
			</React.StrictMode>
		);
	}

	// Back-end WP Admin app mount
	const adminContainer = document.getElementById('preorder-raka-admin-app-root');
	if (adminContainer) {
		const root = createRoot(adminContainer);
		root.render(
			<React.StrictMode>
				<AdminAddPreorder />
			</React.StrictMode>
		);
	}
});

import React, { useState, useEffect, useRef } from 'react';

/**
 * Modern Searchable Select Component (Select2-style Search & Dropdown in React)
 */
export const SearchableSelect = ({
	options = [],
	value,
	onChange,
	placeholder = '-- เลือก --',
	disabled = false,
	renderOption,
	groupBy = false,
}) => {
	const [isOpen, setIsOpen] = useState(false);
	const [searchTerm, setSearchTerm] = useState('');
	const containerRef = useRef(null);
	const searchInputRef = useRef(null);

	// Find currently selected item
	const selectedItem = options.find(opt => String(opt.value) === String(value));

	// Close on click outside
	useEffect(() => {
		const handleClickOutside = (e) => {
			if (containerRef.current && !containerRef.current.contains(e.target)) {
				setIsOpen(false);
			}
		};
		document.addEventListener('mousedown', handleClickOutside);
		return () => document.removeEventListener('mousedown', handleClickOutside);
	}, []);

	// Auto focus search input when opened
	useEffect(() => {
		if (isOpen && searchInputRef.current) {
			searchInputRef.current.focus();
		} else if (!isOpen) {
			setSearchTerm('');
		}
	}, [isOpen]);

	// Filter options
	const filteredOptions = options.filter(opt => {
		if (!searchTerm.trim()) return true;
		const s = searchTerm.toLowerCase();
		const matchLabel = opt.label && opt.label.toLowerCase().includes(s);
		const matchSub = opt.sub && opt.sub.toLowerCase().includes(s);
		const matchGroup = opt.group && opt.group.toLowerCase().includes(s);
		return matchLabel || matchSub || matchGroup;
	});

	// Group filtered options if enabled
	const grouped = groupBy
		? filteredOptions.reduce((acc, opt) => {
				const grp = opt.group || 'อื่น ๆ';
				if (!acc[grp]) acc[grp] = [];
				acc[grp].push(opt);
				return acc;
		  }, {})
		: null;

	const handleSelect = (val) => {
		onChange(val);
		setIsOpen(false);
	};

	const handleClear = (e) => {
		e.stopPropagation();
		onChange('');
		setIsOpen(false);
	};

	return (
		<div
			ref={containerRef}
			style={{
				position: 'relative',
				width: '100%',
				userSelect: 'none',
				fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
			}}
		>
			{/* Trigger Box */}
			<div
				onClick={() => !disabled && setIsOpen(!isOpen)}
				style={{
					minHeight: 44,
					padding: '6px 12px',
					borderRadius: 8,
					border: isOpen ? '1px solid #0f172a' : '1px solid #cbd5e1',
					boxShadow: isOpen ? '0 0 0 2px rgba(15, 23, 42, 0.1)' : 'none',
					background: disabled ? '#f1f5f9' : '#ffffff',
					cursor: disabled ? 'not-allowed' : 'pointer',
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'space-between',
					gap: 8,
					transition: 'all 0.15s ease'
				}}
			>
				<div style={{ flex: 1, minWidth: 0, display: 'flex', alignItems: 'center', gap: 10 }}>
					{selectedItem ? (
						renderOption ? (
							renderOption(selectedItem, true)
						) : (
							<div style={{ display: 'flex', alignItems: 'center', gap: 8, minWidth: 0, overflow: 'hidden' }}>
								{selectedItem.image && (
									<img
										src={selectedItem.image}
										alt=""
										style={{ width: 28, height: 28, objectFit: 'contain', borderRadius: 4, background: '#f8fafc', border: '1px solid #e2e8f0', flexShrink: 0 }}
									/>
								)}
								<div style={{ minWidth: 0, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
									<span style={{ fontSize: 13, fontWeight: 600, color: '#0f172a' }}>{selectedItem.label}</span>
									{selectedItem.sub && (
										<span style={{ marginLeft: 8, fontSize: 12, color: '#64748b' }}>{selectedItem.sub}</span>
									)}
								</div>
							</div>
						)
					) : (
						<span style={{ fontSize: 13, color: '#94a3b8' }}>{placeholder}</span>
					)}
				</div>

				{/* Right icons */}
				<div style={{ display: 'flex', alignItems: 'center', gap: 4, flexShrink: 0 }}>
					{selectedItem && !disabled && (
						<button
							type="button"
							onClick={handleClear}
							style={{
								border: 'none',
								background: 'transparent',
								cursor: 'pointer',
								color: '#94a3b8',
								padding: 2,
								display: 'flex',
								alignItems: 'center',
								borderRadius: 4
							}}
							title="ล้างค่า"
						>
							<i className="ri-close-line" style={{ fontSize: 16 }} />
						</button>
					)}
					<i
						className={isOpen ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line'}
						style={{ fontSize: 18, color: '#64748b' }}
					/>
				</div>
			</div>

			{/* Dropdown Menu */}
			{isOpen && (
				<div
					style={{
						position: 'absolute',
						top: 'calc(100% + 4px)',
						left: 0,
						right: 0,
						zIndex: 99999,
						background: '#ffffff',
						borderRadius: 8,
						border: '1px solid #cbd5e1',
						boxShadow: '0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1)',
						maxHeight: 320,
						display: 'flex',
						flexDirection: 'column',
						overflow: 'hidden'
					}}
				>
					{/* Search Field */}
					<div style={{ padding: '8px 10px', borderBottom: '1px solid #f1f5f9', background: '#f8fafc', display: 'flex', alignItems: 'center', gap: 6 }}>
						<i className="ri-search-line" style={{ color: '#94a3b8', fontSize: 14 }} />
						<input
							ref={searchInputRef}
							type="text"
							value={searchTerm}
							onChange={(e) => setSearchTerm(e.target.value)}
							placeholder="พิมพ์เพื่อค้นหา (ชื่อสินค้า, SKU, หมวดหมู่)..."
							style={{
								flex: 1,
								border: 'none',
								background: 'transparent',
								outline: 'none',
								fontSize: 13,
								padding: '4px 0',
								color: '#0f172a'
							}}
						/>
						{searchTerm && (
							<button
								type="button"
								onClick={() => setSearchTerm('')}
								style={{ border: 'none', background: 'transparent', cursor: 'pointer', color: '#94a3b8', padding: 2 }}
							>
								<i className="ri-close-circle-fill" style={{ fontSize: 14 }} />
							</button>
						)}
					</div>

					{/* Options List */}
					<div style={{ overflowY: 'auto', flex: 1, padding: '4px 0' }}>
						{filteredOptions.length === 0 ? (
							<div style={{ padding: '20px 14px', textAlign: 'center', color: '#94a3b8', fontSize: 13 }}>
								<i className="ri-inbox-line" style={{ fontSize: 24, display: 'block', marginBottom: 4 }} />
								ไม่พบข้อมูลที่ค้นหา "{searchTerm}"
							</div>
						) : groupBy ? (
							Object.keys(grouped).map(groupName => (
								<div key={groupName}>
									<div
										style={{
											padding: '6px 12px',
											fontSize: 11,
											fontWeight: 700,
											color: '#64748b',
											textTransform: 'uppercase',
											letterSpacing: '0.05em',
											background: '#f8fafc',
											borderBottom: '1px solid #f1f5f9',
											borderTop: '1px solid #f1f5f9'
										}}
									>
										{groupName} ({grouped[groupName].length})
									</div>
									{grouped[groupName].map(opt => {
										const isSelected = String(opt.value) === String(value);
										return (
											<div
												key={opt.value}
												onClick={() => handleSelect(opt.value)}
												style={{
													padding: '8px 12px',
													display: 'flex',
													alignItems: 'center',
													justifyContent: 'space-between',
													gap: 10,
													cursor: 'pointer',
													background: isSelected ? '#f1f5f9' : 'transparent',
													transition: 'background 0.1s'
												}}
												onMouseEnter={(e) => {
													if (!isSelected) e.currentTarget.style.background = '#f8fafc';
												}}
												onMouseLeave={(e) => {
													if (!isSelected) e.currentTarget.style.background = 'transparent';
												}}
											>
												{renderOption ? (
													renderOption(opt, false)
												) : (
													<div style={{ display: 'flex', alignItems: 'center', gap: 10, minWidth: 0, flex: 1 }}>
														{opt.image && (
															<img
																src={opt.image}
																alt=""
																style={{ width: 32, height: 32, objectFit: 'contain', borderRadius: 6, background: '#f8fafc', border: '1px solid #e2e8f0', flexShrink: 0 }}
															/>
														)}
														<div style={{ minWidth: 0, flex: 1 }}>
															<div style={{ fontSize: 13, fontWeight: isSelected ? 700 : 500, color: '#0f172a' }}>
																{opt.label}
															</div>
															{opt.sub && (
																<div style={{ fontSize: 11, color: '#64748b', marginTop: 2 }}>
																	{opt.sub}
																</div>
															)}
														</div>
													</div>
												)}
												{isSelected && (
													<i className="ri-check-line" style={{ color: '#0f172a', fontWeight: 'bold', fontSize: 16 }} />
												)}
											</div>
										);
									})}
								</div>
							))
						) : (
							filteredOptions.map(opt => {
								const isSelected = String(opt.value) === String(value);
								return (
									<div
										key={opt.value}
										onClick={() => handleSelect(opt.value)}
										style={{
											padding: '8px 12px',
											display: 'flex',
											alignItems: 'center',
											justifyContent: 'space-between',
											gap: 10,
											cursor: 'pointer',
											background: isSelected ? '#f1f5f9' : 'transparent',
											transition: 'background 0.1s'
										}}
										onMouseEnter={(e) => {
											if (!isSelected) e.currentTarget.style.background = '#f8fafc';
										}}
										onMouseLeave={(e) => {
											if (!isSelected) e.currentTarget.style.background = 'transparent';
										}}
									>
										{renderOption ? (
											renderOption(opt, false)
										) : (
											<div style={{ display: 'flex', alignItems: 'center', gap: 10, minWidth: 0, flex: 1 }}>
												{opt.image && (
													<img
														src={opt.image}
														alt=""
														style={{ width: 32, height: 32, objectFit: 'contain', borderRadius: 6, background: '#f8fafc', border: '1px solid #e2e8f0', flexShrink: 0 }}
													/>
												)}
												<div style={{ minWidth: 0, flex: 1 }}>
													<div style={{ fontSize: 13, fontWeight: isSelected ? 700 : 500, color: '#0f172a' }}>
														{opt.label}
													</div>
													{opt.sub && (
														<div style={{ fontSize: 11, color: '#64748b', marginTop: 2 }}>
															{opt.sub}
														</div>
													)}
												</div>
											</div>
										)}
										{isSelected && (
											<i className="ri-check-line" style={{ color: '#0f172a', fontWeight: 'bold', fontSize: 16 }} />
										)}
									</div>
								);
							})
						)}
					</div>
				</div>
			)}
		</div>
	);
};

export default SearchableSelect;

const fs = require('fs');
const path = require('path');
const { PNG } = require('pngjs');

const inputPath = path.join(__dirname, '../assets/images/angel-hero.png');
const outputPath = path.join(__dirname, '../assets/images/angel-hero.png');

fs.createReadStream(inputPath)
	.pipe(new PNG({ filterType: 4 }))
	.on('parsed', function() {
		for (let y = 0; y < this.height; y++) {
			for (let x = 0; x < this.width; x++) {
				const idx = (this.width * y + x) << 2;
				const r = this.data[idx];
				const g = this.data[idx + 1];
				const b = this.data[idx + 2];

				const max = Math.max(r, g, b);
				const min = Math.min(r, g, b);
				const sat = max - min;

				// Flat cement grey background detection
				if (sat < 18 && r > 165 && r < 252 && g > 165 && g < 252 && b > 165 && b < 252) {
					this.data[idx + 3] = 0; // Transparent!
				}
			}
		}

		this.pack().pipe(fs.createWriteStream(outputPath)).on('finish', () => {
			console.log('Successfully created transparent angel image at', outputPath);
		});
	});

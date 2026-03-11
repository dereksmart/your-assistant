( function() {
	const canvas = document.getElementById( 'kevins-balcony-canvas' );
	const ctx    = canvas.getContext( '2d' );
	const W = canvas.width;
	const H = canvas.height;

	const LEDGE_Y      = H - 80;
	const LEDGE_H      = 18;
	const CAT_W        = 36;
	const CAT_H        = 28;
	const CAT_SPEED    = 4;
	const HAND_W       = 30;
	const HAND_H       = 50;
	const FALL_GRAVITY = 0.5;

	let state, cat, hands, wind, score, animId, windTimer, handTimer, diffTimer;

	function init() {
		state = 'playing'; // playing | grabbed | fallen | won
		cat = {
			x: W / 2,
			y: LEDGE_Y - CAT_H,
			vy: 0,
			falling: false,
		};
		hands  = [];
		wind   = 0;
		score  = 0;
		windTimer  = 0;
		handTimer  = 90;
		diffTimer  = 0;
		document.getElementById( 'kb-restart' ).style.display = 'none';
		keys = {};
		cancelAnimationFrame( animId );
		loop();
	}

	let keys = {};
	document.addEventListener( 'keydown', e => { keys[ e.key ] = true; } );
	document.addEventListener( 'keyup',   e => { keys[ e.key ] = false; } );

	// Mobile tap
	let touchStartX = null;
	canvas.addEventListener( 'touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true } );
	canvas.addEventListener( 'touchend', e => {
		if ( touchStartX === null ) return;
		const dx = e.changedTouches[0].clientX - touchStartX;
		if ( dx > 20 )       keys['ArrowRight'] = true;
		else if ( dx < -20 ) keys['ArrowLeft']  = true;
		setTimeout( () => { keys['ArrowRight'] = keys['ArrowLeft'] = false; }, 100 );
		touchStartX = null;
	}, { passive: true } );

	document.getElementById( 'kb-restart' ).addEventListener( 'click', init );

	function spawnHand() {
		// Hands drop from the top, aimed at Kevin's current position
		const speed = 2 + Math.floor( diffTimer / 300 ) * 0.4;
		hands.push( {
			x:     cat.x + ( Math.random() * 100 - 50 ), // aim near Kevin with some offset
			y:     -HAND_H,                              // start above canvas
			speedY: speed,
			phase: 'dropping',  // dropping | hovering | retreating
			hoverTimer: 0,
		} );
	}

	function update() {
		if ( state !== 'playing' ) return;

		score++;
		diffTimer++;
		windTimer++;
		handTimer--;

		// Spawn hands more frequently over time
		const spawnInterval = Math.max( 30, 90 - Math.floor( diffTimer / 300 ) * 8 );
		if ( handTimer <= 0 ) {
			spawnHand();
			handTimer = spawnInterval;
		}

		// Wind shifts every ~5 seconds
		if ( windTimer > 300 ) {
			wind = ( Math.random() * 2 - 1 ) * ( 1 + score / 1000 );
			windTimer = 0;
		}

		// Move cat
		if ( !cat.falling ) {
			if ( keys['ArrowLeft'] )  cat.x -= CAT_SPEED;
			if ( keys['ArrowRight'] ) cat.x += CAT_SPEED;
			cat.x += wind * 0.4;
		}

		// Ledge bounds — fall off edge
		if ( cat.x < 0 || cat.x + CAT_W > W ) {
			cat.falling = true;
		}

		// Falling physics
		if ( cat.falling ) {
			cat.vy += FALL_GRAVITY;
			cat.y  += cat.vy;
			if ( cat.y > H + 50 ) {
				state = 'fallen';
				endGame();
				return;
			}
		}

		// Update hands
		for ( let i = hands.length - 1; i >= 0; i-- ) {
			const h = hands[ i ];

			if ( h.phase === 'dropping' ) {
				h.y += h.speedY;
				// Stop just above the ledge
				if ( h.y >= LEDGE_Y - HAND_H - 5 ) {
					h.y = LEDGE_Y - HAND_H - 5;
					h.phase = 'hovering';
					h.hoverTimer = 60 + Math.random() * 60; // hover for ~1-2s
				}
			} else if ( h.phase === 'hovering' ) {
				// Slowly track Kevin horizontally while hovering
				const dx = ( cat.x + CAT_W / 2 ) - ( h.x + HAND_W / 2 );
				h.x += Math.sign( dx ) * Math.min( Math.abs( dx ), 1.5 );
				h.hoverTimer--;
				if ( h.hoverTimer <= 0 ) {
					h.phase = 'retreating';
				}
			} else if ( h.phase === 'retreating' ) {
				h.y -= h.speedY * 1.5;
				if ( h.y < -HAND_H ) {
					hands.splice( i, 1 );
					continue;
				}
			}

			// Check grab — only while hovering
			if (
				h.phase === 'hovering' &&
				!cat.falling &&
				Math.abs( ( h.x + HAND_W / 2 ) - ( cat.x + CAT_W / 2 ) ) < CAT_W * 0.8 &&
				h.y + HAND_H >= cat.y
			) {
				state = 'grabbed';
				endGame();
				return;
			}
		}

		// Win condition — survive 2000 ticks (~33 seconds)
		if ( score >= 2000 ) {
			state = 'won';
			endGame();
		}
	}

	function drawBackground() {
		// Sky gradient
		const sky = ctx.createLinearGradient( 0, 0, 0, H );
		sky.addColorStop( 0, '#87CEEB' );
		sky.addColorStop( 1, '#d0eaf8' );
		ctx.fillStyle = sky;
		ctx.fillRect( 0, 0, W, H );

		// A distant cityscape silhouette
		ctx.fillStyle = '#b0c8d8';
		const buildings = [ [20,60,50,120],[80,40,60,140],[150,70,40,110],[200,30,55,150],[270,55,45,125],[330,45,50,135],[390,65,40,115],[440,35,60,145],[510,60,50,120] ];
		buildings.forEach( ( [x, h, w] ) => {
			ctx.fillRect( x, H - 140 - h, w, h + 80 );
		} );

		// Ground
		ctx.fillStyle = '#5a8a3c';
		ctx.fillRect( 0, H - 40, W, 40 );
	}

	function drawLedge() {
		// Fence posts
		ctx.fillStyle = '#8B6914';
		for ( let x = 10; x < W; x += 40 ) {
			ctx.fillRect( x, LEDGE_Y - 50, 8, 50 + LEDGE_H );
		}
		// Horizontal rail
		ctx.fillStyle = '#A0784A';
		ctx.fillRect( 0, LEDGE_Y, W, LEDGE_H );

		// Lattice netting (the famous fence)
		ctx.strokeStyle = 'rgba(180,140,80,0.5)';
		ctx.lineWidth = 1;
		for ( let x = 0; x < W; x += 20 ) {
			ctx.beginPath(); ctx.moveTo( x, LEDGE_Y - 50 ); ctx.lineTo( x + 20, LEDGE_Y ); ctx.stroke();
			ctx.beginPath(); ctx.moveTo( x + 20, LEDGE_Y - 50 ); ctx.lineTo( x, LEDGE_Y ); ctx.stroke();
		}
	}

	function drawCat() {
		const x = cat.x;
		const y = cat.y;

		// Body
		ctx.fillStyle = '#888';
		ctx.beginPath();
		ctx.ellipse( x + CAT_W / 2, y + CAT_H * 0.6, CAT_W * 0.45, CAT_H * 0.38, 0, 0, Math.PI * 2 );
		ctx.fill();

		// Head
		ctx.fillStyle = '#888';
		ctx.beginPath();
		ctx.arc( x + CAT_W / 2, y + 10, 11, 0, Math.PI * 2 );
		ctx.fill();

		// Ears
		ctx.fillStyle = '#888';
		ctx.beginPath();
		ctx.moveTo( x + CAT_W / 2 - 9, y + 4 );
		ctx.lineTo( x + CAT_W / 2 - 4, y - 5 );
		ctx.lineTo( x + CAT_W / 2,     y + 2 );
		ctx.fill();
		ctx.beginPath();
		ctx.moveTo( x + CAT_W / 2 + 9, y + 4 );
		ctx.lineTo( x + CAT_W / 2 + 4, y - 5 );
		ctx.lineTo( x + CAT_W / 2,     y + 2 );
		ctx.fill();

		// Eyes
		ctx.fillStyle = '#fff';
		ctx.beginPath(); ctx.arc( x + CAT_W / 2 - 4, y + 9, 3, 0, Math.PI * 2 ); ctx.fill();
		ctx.beginPath(); ctx.arc( x + CAT_W / 2 + 4, y + 9, 3, 0, Math.PI * 2 ); ctx.fill();
		ctx.fillStyle = '#222';
		ctx.beginPath(); ctx.arc( x + CAT_W / 2 - 4, y + 9, 1.5, 0, Math.PI * 2 ); ctx.fill();
		ctx.beginPath(); ctx.arc( x + CAT_W / 2 + 4, y + 9, 1.5, 0, Math.PI * 2 ); ctx.fill();

		// Tail
		ctx.strokeStyle = '#888';
		ctx.lineWidth = 4;
		ctx.beginPath();
		ctx.moveTo( x + CAT_W, y + CAT_H * 0.6 );
		ctx.quadraticCurveTo( x + CAT_W + 18, y + CAT_H * 0.3, x + CAT_W + 12, y + CAT_H * 0.05 );
		ctx.stroke();
	}

	function drawHands() {
		hands.forEach( h => {
			// Arm
			ctx.fillStyle = '#f4c2a1';
			ctx.fillRect( h.x - HAND_W / 2, h.y, HAND_W, HAND_H );

			// Hand blob
			ctx.fillStyle = '#f4c2a1';
			ctx.beginPath();
			ctx.ellipse( h.x, h.y + 8, HAND_W * 0.6, 14, 0, 0, Math.PI * 2 );
			ctx.fill();

			// Fingers
			for ( let f = -1; f <= 1; f++ ) {
				ctx.beginPath();
				ctx.ellipse( h.x + f * 9, h.y, 4, 10, 0, 0, Math.PI * 2 );
				ctx.fill();
			}
		} );
	}

	function drawHUD() {
		ctx.fillStyle = 'rgba(0,0,0,0.35)';
		ctx.fillRect( 8, 8, 130, 28 );
		ctx.fillStyle = '#fff';
		ctx.font = 'bold 14px monospace';
		ctx.fillText( '⏱ ' + Math.floor( score / 60 ) + 's  survived', 16, 27 );

		if ( wind > 0.3 ) {
			ctx.fillStyle = 'rgba(255,255,255,0.7)';
			ctx.font = '13px monospace';
			ctx.fillText( '💨 wind →', W - 100, 26 );
		} else if ( wind < -0.3 ) {
			ctx.fillStyle = 'rgba(255,255,255,0.7)';
			ctx.font = '13px monospace';
			ctx.fillText( '💨 ← wind', W - 100, 26 );
		}
	}

	function drawOverlay( title, line1, line2 ) {
		// Dark overlay
		ctx.fillStyle = 'rgba(0,0,0,0.65)';
		ctx.fillRect( 0, 0, W, H );

		ctx.textAlign = 'center';
		ctx.textBaseline = 'middle';

		// Title
		ctx.fillStyle = '#ffffff';
		ctx.font = 'bold 32px sans-serif';
		ctx.fillText( title, W / 2, H / 2 - 50 );

		// Subtitle line 1
		ctx.fillStyle = '#eeeeee';
		ctx.font = 'italic 17px sans-serif';
		ctx.fillText( line1, W / 2, H / 2 );

		// Subtitle line 2 (score)
		ctx.fillStyle = '#aaaaaa';
		ctx.font = '14px monospace';
		ctx.fillText( line2, W / 2, H / 2 + 40 );

		ctx.textAlign = 'left';
		ctx.textBaseline = 'alphabetic';
	}

	function endGame() {
		cancelAnimationFrame( animId );
		// Draw the final game frame first, then overlay on top
		draw();
		const survived = 'survived ' + Math.floor( score / 60 ) + ' seconds';
		if ( state === 'grabbed' ) {
			drawOverlay( '😿 Grabbed!', "She didn't trust what I was doing.", survived );
		} else if ( state === 'fallen' ) {
			drawOverlay( '🌿 Fell!', 'Landed unscathed but shaken.', survived );
		} else if ( state === 'won' ) {
			drawOverlay( '🎉 You made it!', 'Derek dismantled the fence. Good cat.', survived );
		}
		document.getElementById( 'kb-restart' ).style.display = 'inline-block';
	}

	function draw() {
		ctx.clearRect( 0, 0, W, H );
		drawBackground();
		drawLedge();
		drawHands();
		drawCat();
		drawHUD();
	}

	function loop() {
		update();
		draw();
		if ( state === 'playing' ) {
			animId = requestAnimationFrame( loop );
		}
	}

	init();
} )();

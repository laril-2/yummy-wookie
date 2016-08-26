package model;

import java.awt.image.BufferedImage;
import java.awt.event.KeyEvent;
import java.awt.event.MouseEvent;
import javax.imageio.ImageIO;
import java.io.File;
import java.io.IOException;

import app.Board;

public class Player {

	private double x;
	private double y;
	private double dx;
	private double dy;
	private double rotation;

	private BufferedImage image;

	private Board board;

	public Player(Board b) throws IOException {
		initPlayer(b);
	}

	private void initPlayer(Board b) throws IOException {
		image = null;
		image = ImageIO.read(new File("images/debian.png"));

		x = 140.0;
		y = 140.0;
		rotation = -0.5 * Math.PI;

		board = b;
	}

	public void update() {
		boolean[] keys = board.getKeyStates();

		if (keys[0]) {	// LEFT
			rotation -= 0.1;
		}
		if (keys[1]) {	// RIGHT
			rotation += 0.1;
		}
		if (keys[2]) {	// UP
			dx += Math.cos(rotation) * 0.05;
			dy += Math.sin(rotation) * 0.05;
		}

		dy += 0.01;

		x += dx;
		y += dy;

		if (x < 0 || x > board.getWidth() || y < 0 || y > board.getHeight()) {
			System.exit(0);
		} 
	}

	public int getX() {
		return (int) x;
	}

	public int getY() {
		return (int) y;
	}

	public double getRotation() {
		return rotation;
	}

	public BufferedImage getImage() {
		return image;
	}

	public void mouseClicked(MouseEvent e) {
		x = 40;
	}
}

package model;

import app.Board;

public class Mass {

	private static final double FRICTION = 0.95;

	private double x, y, lastX, lastY, fX, fY;
	private double mass, radius;

	public Mass(double x, double y, double m) {
		lastX = x;
		lastY = y;
		this.x = x;
		this.y = y;

		radius = m;
		mass = m;

		fX = 0;
		fY = 0;
	}

	public double x() {
		return x;
	}

	public double y() {
		return y;
	}

	public double lastX() {
		return lastX;
	}

	public double lastY() {
		return lastY;
	}

	public double radius() {
		return radius;
	}

	public double mass() {
		return mass;
	}

	public void addForce(double x, double y) {
		fX += x;
		fY += y;
	}

	public void update(double dt, Board board) {
		double tempX = x;
		double tempY = y;

		x += (x - lastX) + (fX / mass) * dt * dt;
		y += (y - lastY) + (fY / mass) * dt * dt;

		fX = 0;
		fY = 0;

		lastX = tempX;
		lastY = tempY;

		board.collide(this);
	}

	public void reflect(double nx, double ny) {
		addForce(nx * mass * 20, ny * mass * 20);

		double dx = x - lastX;
		double dy = y - lastY;

		x = lastX + 0.95 * dx;
		y = lastY + 0.95 * dy;
	}

	/*
	// expects input vector to be normalized !!!
	public void reflect(double nx, double ny) {
		double dx = x - lastX;
		double dy = y - lastY;

		double dot = dx * nx + dy * ny;

		double rx = dx - 2 * dot * nx;
		double ry = dy - 2 * dot * ny;

		x = lastX + rx;
		y = lastY + ry;
	}
	*/
}

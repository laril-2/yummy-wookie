package model;

public class Mass {

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

	public double radius() {
		return radius;
	}

	public void addForce(double x, double y) {
		fX += x;
		fY += y;
	}

	public void update(double dt) {
		double tempX = x;
		double tempY = y;

		x += (x - lastX) + (fX / mass) * dt * dt;
		y += (y - lastY) + (fY / mass) * dt * dt;

		lastX = tempX;
		lastY = tempY;

		fX = 0;
		fY = 0;
	}

	public void revert() {
		x = lastX;
		y = lastY;
	}
}

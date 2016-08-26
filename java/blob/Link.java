package model;

public class Link {

	private Mass a, b;

	private double restLength, stiffness;

	public Link(double rl, double k) {
		a = null;
		b = null;

		restLength = rl;
		stiffness = k;
		if (restLength < 5)	// TODO magic number
			restLength = 5;
		if (stiffness < 0.01)	// TODO magic number
			stiffness = 0.01;
	}

	public boolean isComplete() {
		return (a != null && b != null);
	}

	public Mass a() {
		return a;
	}

	public Mass b() {
		return b;
	}

	public boolean join(Mass m) {
		if (m == null)
			return false;

		if (a == null) {
			a = m;
			return true;
		}
		else if (b == null && a != m) {
			b = m;
			return true;
		}

		return false;
	}

	public void detach() {
		a = null;
		b = null;
	}

	public void update() {
		if (!isComplete())
			return;
		
		double dx = b.x() - a.x();
		double dy = b.y() - a.y();

		double length = Math.sqrt((dx * dx) + (dy * dy));

		if (length < 0.0001) { // TODO magic number
			return;
		}

		// normalize
		dx = dx / length;
		dy = dy / length;

		double deviation = length - restLength;
		double coefficient = deviation * stiffness;

		a.addForce(dx * coefficient, dy * coefficient);
		b.addForce(-dx * coefficient, -dy * coefficient);
	}
}

package model;

public class Link {

	private Mass a, b;

	private double restLength, stiffness, damping, lastLength;

	private boolean contracted;

	public Link(double restLength, double stiffness, double damping) {
		a = null;
		b = null;
		lastLength = -1;
		contracted = false;

		this.restLength = restLength;
		this.stiffness = stiffness;
		this.damping = damping;
		if (this.restLength < 5)	// TODO magic number
			this.restLength = 5;
		if (this.stiffness < 0.01)	// TODO magic number
			this.stiffness = 0.01;
		if (this.damping < 0)
			this.damping = 0;
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
		lastLength = -1;
	}

	public void setContracted(boolean contracted) {
		this.contracted = contracted;
	}

	private double getRestLength() {
		if (contracted)
			return 0.8 * restLength; // TODO magic number
		return restLength;
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

		double deviation = length - getRestLength();
		double coefficient = deviation * stiffness;

		a.addForce(dx * coefficient, dy * coefficient);
		b.addForce(-dx * coefficient, -dy * coefficient);

		if (lastLength > 0) {
			coefficient = (length - lastLength) * damping;

			a.addForce(dx * coefficient, dy * coefficient);
			b.addForce(-dx * coefficient, -dy * coefficient);
		}
		lastLength = length;
	}
}

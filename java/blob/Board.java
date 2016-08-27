package app;

import java.awt.Color;
import java.awt.BasicStroke;
import java.awt.Graphics;
import java.awt.Graphics2D;
import java.awt.image.BufferedImage;
import java.awt.Toolkit;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.awt.event.KeyAdapter;
import java.awt.event.MouseAdapter;
import java.awt.event.KeyEvent;
import java.awt.event.MouseEvent;
import java.awt.geom.Ellipse2D;
import java.awt.geom.Line2D;
import javax.swing.JPanel;
import javax.swing.Timer;
import java.io.IOException;
import java.util.ArrayList;

import model.Mass;
import model.Link;

public class Board extends JPanel implements ActionListener {

	private final int DELAY = 20;
	private final int STEP_COUNT = 5; // 5
	private final double TIMESTEP = 0.05; // 0.05

	private final int BLOB_SEGMENT_COUNT = 20; // 20
	private final double BLOB_RADIUS = 120.0; // 120.0
	private final double BLOB_SKIN_THICKNESS = 40.0; // 40.0
	private final double BLOB_DAMPING = 10.0; // 10.0
	private final double BLOB_SKIN_STIFFNESS = 10.0; // 10.0
	private final double BLOB_INNER_STIFFNESS = 5.0; // 5.0
	private final double BLOB_CENTER_MASS = 20.0; // 20.0
	private final double BLOB_OUTER_MASS = 3.0;	// 3.0
	private final double THRUSTER_FORCE = 50.0;

	private static final double GRAVITY = 0.6; // 0.6

	private Timer timer;

	private boolean[] keydown = new boolean[4];

	private ArrayList<Mass> masses; 
	private ArrayList<Link> links; 

	private Mass centerMass = null;

	public Board() {
		initBoard();
	}

	private void initBoard() {
		addKeyListener(new TAdapter());
		addMouseListener(new MAdapter());
		setFocusable(true);
		setBackground(Color.BLACK);

		initWorld();

		timer = new Timer(DELAY, this);
		timer.start();
	}

	private void initWorld() {
		masses = new ArrayList<Mass>();
		links = new ArrayList<Link>();
		centerMass = null;

		addBlob(500, 598);
	}

	private void addBlob(double cx, double cy) {
		centerMass = new Mass(cx, cy, BLOB_CENTER_MASS);
		masses.add(centerMass);

		double radius = BLOB_RADIUS;
		double skinThickness = BLOB_SKIN_THICKNESS;
		int segments = BLOB_SEGMENT_COUNT;

		double innerSegment = 2 * Math.PI * radius / segments;
		double outerSegment = 2 * Math.PI * (radius + skinThickness) / segments;
		double newSkinSegment = 2 * Math.PI * (radius - skinThickness / 2) / segments;
		double obliqueLength = Math.sqrt(skinThickness * skinThickness + innerSegment * innerSegment);
		double newObliqueLength = Math.sqrt(0.25 * skinThickness + newSkinSegment * newSkinSegment);
		Mass[] inners = new Mass[segments];
		Mass[] outers = new Mass[segments];
		Mass[] news = new Mass[segments];
		double angle = 2 * Math.PI / segments;

		for (int i = 0; i < segments; i++) {
			double x = cx + radius * Math.cos(angle * i);
			double y = cy + radius * Math.sin(angle * i);

			inners[i] = new Mass(x, y, BLOB_OUTER_MASS);
			masses.add(inners[i]);

			Link l = new Link(radius, BLOB_INNER_STIFFNESS, BLOB_DAMPING);
			l.join(centerMass);
			l.join(inners[i]);
			links.add(l);
		}
		for (int i = 0; i < segments; i++) {
			double x = cx + (radius - skinThickness / 2) * Math.cos(angle * i);
			double y = cy + (radius - skinThickness / 2) * Math.sin(angle * i);

			news[i] = new Mass(x, y, BLOB_OUTER_MASS);
			masses.add(news[i]);

			Link l = new Link(radius - skinThickness / 2, BLOB_INNER_STIFFNESS, BLOB_DAMPING);
			l.join(centerMass);
			l.join(news[i]);
			links.add(l);
		}
		for (int i = 0; i < segments; i++) {
			double x = cx + (radius + skinThickness) * Math.cos(angle * i);
			double y = cy + (radius + skinThickness) * Math.sin(angle * i);

			outers[i] = new Mass(x, y, 3.0);
			masses.add(outers[i]);
		}

		for (int i = 0; i < segments; i++) {
			int next = (i + 1) % segments;

			Link l = new Link(innerSegment, BLOB_SKIN_STIFFNESS, BLOB_DAMPING);
			l.join(inners[i]);
			l.join(inners[next]);
			links.add(l);

			l = new Link(outerSegment, BLOB_SKIN_STIFFNESS, BLOB_DAMPING);
			l.join(outers[i]);
			l.join(outers[next]);
			links.add(l);

			l = new Link(skinThickness, BLOB_SKIN_STIFFNESS, BLOB_DAMPING);
			l.join(inners[i]);
			l.join(outers[i]);
			links.add(l);

			l = new Link(obliqueLength, BLOB_SKIN_STIFFNESS, BLOB_DAMPING);
			l.join(outers[i]);
			l.join(inners[next]);
			links.add(l);

			l = new Link(obliqueLength, BLOB_SKIN_STIFFNESS, BLOB_DAMPING);
			l.join(outers[next]);
			l.join(inners[i]);
			links.add(l);

			l = new Link(newObliqueLength, BLOB_SKIN_STIFFNESS, BLOB_DAMPING);
			l.join(news[i]);
			l.join(inners[next]);
			links.add(l);

			l = new Link(newObliqueLength, BLOB_SKIN_STIFFNESS, BLOB_DAMPING);
			l.join(news[next]);
			l.join(inners[i]);
			links.add(l);
		}
	}

	public boolean[] getKeyStates() {
		return keydown;
	}

	@Override
	public void paintComponent(Graphics g) {
		super.paintComponent(g);

		doDrawing(g);

		Toolkit.getDefaultToolkit().sync();
	}

	private void doDrawing(Graphics g) {
		Graphics2D g2d = (Graphics2D) g;

		g2d.setPaint(Color.GREEN);
		g2d.setStroke(new BasicStroke(1.0f));

		for (Link l : links) {
			if (!l.isComplete())
				continue;

			g2d.draw(new Line2D.Double(l.a().x(), l.a().y(), l.b().x(), l.b().y()));
		}

		g2d.setPaint(Color.BLUE);
		g2d.setStroke(new BasicStroke(3.0f));

		for (Mass m : masses) {
			double r = m.radius();
			g2d.draw(new Ellipse2D.Double(m.x() - r, m.y() - r, 2 * r, 2 * r));
		}

		/*
		if (player != null) {
			BufferedImage image = player.getImage();
			int rotationX = player.getX();
			int rotationY = player.getY();
			g2d.rotate(player.getRotation(), rotationX, rotationY);
			g2d.drawImage(player.getImage(), null, player.getX() - image.getWidth() / 2, player.getY() - image.getHeight() / 2);
			g2d.rotate(player.getRotation(), rotationX, rotationY);
		}
		*/
	}

	@Override
	public void actionPerformed(ActionEvent e) {
		for (int i = 0; i < STEP_COUNT; i++) {

			// Links
			for (Link l : links) {
				l.update();
			}

			// GRAVITY
			for (Mass m : masses) {
				m.addForce(0, GRAVITY * m.mass());
			}

			if (centerMass != null) {
				if (keydown[0])
					centerMass.addForce(-THRUSTER_FORCE, 0);
				if (keydown[1])
					centerMass.addForce(THRUSTER_FORCE, 0);
				if (keydown[2])
					centerMass.addForce(0, -THRUSTER_FORCE);
				if (keydown[3])
					centerMass.addForce(0, THRUSTER_FORCE);
			}

			for (Mass m : masses) {
				m.update(TIMESTEP);
			}

			for (Mass m : masses) {
				if (m.x() < 0 || m.x() > getWidth())
					m.revertHorizontal();
 				if (m.y() < 0 || m.y() > getHeight())
					m.revertVertical();
			}
		}

		repaint();
	}

	private class TAdapter extends KeyAdapter {
		
		@Override
		public void keyPressed(KeyEvent e) {
			switch (e.getKeyCode()) {
				case KeyEvent.VK_LEFT:
					keydown[0] = true;
					break;
				case KeyEvent.VK_RIGHT:
					keydown[1] = true;
					break;
				case KeyEvent.VK_UP:
					keydown[2] = true;
					break;
				case KeyEvent.VK_DOWN:
					keydown[3] = true;
					break;
				case KeyEvent.VK_ESCAPE:
					System.exit(0);
					break;
				case KeyEvent.VK_F1:
					initWorld();
					break;
				case KeyEvent.VK_SPACE:
					if (centerMass != null) {
						for (Link l : links) {
							if (l.a() == centerMass || l.b() == centerMass)
								l.setContracted(true);
						}
					}
					break;
			}
		}

		@Override
		public void keyReleased(KeyEvent e) {
			switch (e.getKeyCode()) {
				case KeyEvent.VK_LEFT:
					keydown[0] = false;
					break;
				case KeyEvent.VK_RIGHT:
					keydown[1] = false;
					break;
				case KeyEvent.VK_UP:
					keydown[2] = false;
					break;
				case KeyEvent.VK_DOWN:
					keydown[3] = false;
					break;
				case KeyEvent.VK_SPACE:
					if (centerMass != null) {
						for (Link l : links) {
							if (l.a() == centerMass || l.b() == centerMass)
								l.setContracted(false);
						}
					}
					break;
			}
		}

	}

	private class MAdapter extends MouseAdapter {

		@Override
		public void mouseClicked(MouseEvent e) {
		}

	}

}


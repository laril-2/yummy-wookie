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
	private final double TIMESTEP = 0.1;
	private final double BLOB_STIFFNESS = 1.0;

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

		masses = new ArrayList<Mass>();
		links = new ArrayList<Link>();

		addBlob(500, 350);

		timer = new Timer(DELAY, this);
		timer.start();
	}

	private void addBlob(double cx, double cy) {
		centerMass = new Mass(cx, cy, 20);
		masses.add(centerMass);

		double radius = 150;
		double skinThickness = 20;
		int segments = 20;

		double innerSegment = 2 * Math.PI * radius / segments;
		double outerSegment = 2 * Math.PI * (radius + skinThickness) / segments;
		double obliqueLength = Math.sqrt(skinThickness * skinThickness + innerSegment * innerSegment);
		Mass[] inners = new Mass[segments];
		Mass[] outers = new Mass[segments];
		double angle = 2 * Math.PI / segments;

		for (int i = 0; i < segments; i++) {
			double x = cx + radius * Math.cos(angle * i);
			double y = cy + radius * Math.sin(angle * i);

			inners[i] = new Mass(x, y, 3.0);
			masses.add(inners[i]);

			Link l = new Link(radius, BLOB_STIFFNESS);
			l.join(centerMass);
			l.join(inners[i]);
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

			Link l = new Link(innerSegment, BLOB_STIFFNESS);
			l.join(inners[i]);
			l.join(inners[next]);
			links.add(l);

			l = new Link(outerSegment, BLOB_STIFFNESS);
			l.join(outers[i]);
			l.join(outers[next]);
			links.add(l);

			l = new Link(skinThickness, BLOB_STIFFNESS);
			l.join(inners[i]);
			l.join(outers[i]);
			links.add(l);

			l = new Link(obliqueLength, BLOB_STIFFNESS);
			l.join(outers[i]);
			l.join(inners[next]);
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

		for (Link l : links) {
			l.update();
		}

		if (centerMass != null) {
			if (keydown[0])
				centerMass.addForce(-30, 0);
			if (keydown[1])
				centerMass.addForce(30, 0);
			if (keydown[2])
				centerMass.addForce(0, -30);
			if (keydown[3])
				centerMass.addForce(0, 30);
		}

		for (Mass m : masses) {
			m.update(TIMESTEP);
		}

		for (Mass m : masses) {
			if (m.x() < 0 || m.x() > getWidth() || m.y() < 0 || m.y() > getHeight())
				m.revert();
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
			}
		}

	}

	private class MAdapter extends MouseAdapter {

		@Override
		public void mouseClicked(MouseEvent e) {
		}

	}

}


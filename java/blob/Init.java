package app;

import java.awt.EventQueue;
import javax.swing.JFrame;

public class Init extends JFrame {

	public Init() {
		initUI();
	}

	private void initUI() {
		add(new Board());

		setSize(1100, 800);

		setTitle("Application");
		setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
		setLocationRelativeTo(null);
	}

	public static void main(String[] args) {
		EventQueue.invokeLater(new Runnable() {
			@Override
			public void run() {
				Init ex = new Init();
				ex.setVisible(true);
			}
		});
	}

}

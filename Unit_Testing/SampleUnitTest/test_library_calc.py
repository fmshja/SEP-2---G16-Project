import unittest
import library_calcfunc


class TestCalc(unittest.TestCase):

    def test_add(self):
        self.assertEqual(library_calcfunc.add(5, 5), 10)
        self.assertEqual(library_calcfunc.add(-2, 1), -1)
        self.assertEqual(library_calcfunc.add(-3, -1), -4)

    def test_subtract(self):
        self.assertEqual(library_calcfunc.subtract(10, 5), 5)
        self.assertEqual(library_calcfunc.subtract(-1, 1), -2)
        self.assertEqual(library_calcfunc.subtract(-1, -1), 0)

    def test_multiply(self):
        self.assertEqual(library_calcfunc.multiply(10, 5), 50)
        self.assertEqual(library_calcfunc.multiply(-1, 1), -1)
        self.assertEqual(library_calcfunc.multiply(-1, -1), 1)

    def test_divide(self):
        self.assertEqual(library_calcfunc.divide(10, 5), 2)
        self.assertEqual(library_calcfunc.divide(-1, 1), -1)
        self.assertEqual(library_calcfunc.divide(-1, -1), 1)
        self.assertEqual(library_calcfunc.divide(5, 2), 2.5)

        with self.assertRaises(ValueError):
            library_calcfunc.divide(10, 0)


if __name__ == '__main__':
    unittest.main()
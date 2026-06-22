import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:mobile_app_bmn/main.dart';

void main() {
  testWidgets('BMN App smoke test', (WidgetTester tester) async {
    // Build our app and trigger a frame.
    await tester.pumpWidget(
      const ProviderScope(
        child: BMNApp(),
      ),
    );

    // Wait for initial frame
    await tester.pumpAndSettle();

    // Verify app launches (we should see the login screen)
    expect(find.text('BMN Mobile'), findsOneWidget);
  });
}

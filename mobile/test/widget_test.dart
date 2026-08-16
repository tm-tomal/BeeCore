import 'package:flutter_test/flutter_test.dart';

import 'package:mobile/main.dart';

void main() {
  testWidgets('BeeCore app renders the customer portal shell', (tester) async {
    await tester.pumpWidget(const BeeCoreApp());

    expect(find.text('BeeCore'), findsWidgets);
    expect(find.text('Customer portal'), findsOneWidget);
    expect(find.text('Internet'), findsOneWidget);
  });
}

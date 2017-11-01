import React from 'react';
import TestUtils from 'react-dom/test-utils';
import { renderToStaticMarkup } from 'react-dom/server';

describe('UI: NavFrame component', () => {
  const frame = require('DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/frame.js');

  describe('NavFrame', () => {
    const NavFrame = frame.NavFrame;

    it('should accept "inner" and "outer" parts HTML', () => {
      const html = renderToStaticMarkup(
        <NavFrame>
          <div part="inner"><i>Inner part content</i></div>
          <div part="outer"><p>Outer part content</p></div>
        </NavFrame>
      );
      expect(html).toContain('<i>Inner part content</i>');
      expect(html).toContain('<p>Outer part content</p>');
    });

    it("should render its' children as inner part if parts are omitted", () => {
      const component = TestUtils.renderIntoDocument(<NavFrame>Content</NavFrame>);
      const inner = TestUtils.findRenderedDOMComponentWithTag(component, 'aside');
      expect(inner.textContent).toEqual('Content');
    });
  });

  describe('NavFrameHeader', () => {
    const NavFrameHeader = frame.NavFrameHeader;

    it("should render its' content inside h1 tag", () => {
      const component = TestUtils.renderIntoDocument(<NavFrameHeader>Header text</NavFrameHeader>);
      const h1 = TestUtils.findRenderedDOMComponentWithTag(component, 'h1');
      expect(h1.textContent).toEqual('Header text');
    });

    it('should render an icon passed via the "icon" property', () => {
      const component = TestUtils.renderIntoDocument(<NavFrameHeader icon="test-icon-class" />);
      const icons = TestUtils.scryRenderedDOMComponentsWithClass(component, 'test-icon-class');
      expect(icons.length).toEqual(1);
    });
  });
});

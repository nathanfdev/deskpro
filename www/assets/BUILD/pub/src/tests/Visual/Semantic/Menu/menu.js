import React from 'react';
import { storiesOf } from '@kadira/storybook';
import { MenuWrapper } from 'Semantic/Menu';
import Accordion from 'Semantic/Accordion/Accordion';
import List from 'Semantic/List/List';
import {
  customStructure,
  emailStructure,
  listStructure,
  phrasesStructure,
  primaryList,
  variablesStructure
} from '../../../DemoState/Semantic/menu';

storiesOf('Semantic: menu', module)
  .add(
    'Email Menu',
    () => <MenuWrapper {...emailStructure} />
  )
  .add(
    'Phrases Menu',
    () => <MenuWrapper {...phrasesStructure} />
  )
  .add(
    'Variables Menu',
    () => <MenuWrapper {...variablesStructure} />
  )
  .add(
    'With side panel',
    () => <table className="menu-with-panel">
      <tr>
        <td><MenuWrapper {...emailStructure} /></td>
        <td className="panel">
          <h4>Primary</h4>
          <List {...primaryList} />
          <h4>Custom</h4>
          <Accordion {...customStructure} />
          <h4>Additional templates</h4>
          <Accordion {...listStructure} />
        </td>
      </tr>
    </table>
  )
;

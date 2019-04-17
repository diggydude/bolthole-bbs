function Node(value, tree)
{

  this.tree       = tree;
  this.value      = value;
  this.nodeId     = null;
  this.parentId   = 0;
  this.childNodes = [];
  if ("id" in value) {
    this.nodeId = value.id;
  }
  if ("parentId" in value) {
    this.parentId = value.parentId;
  }
  if (this.nodeId == null) {
    this.nodeId = String.random(32);
  }

  this.setValue       = function(value)
                        {
                          oldVal = this.value;
                          this.value = value;
                          return oldVal;
                        }; // setValue

  this.getParent      = function()
                        {
                          return this.tree.getNodeById(this.parentId);
                        }; // getParent

  this.getChild       = function(index)
                        {
                          return (index < this.childNodes.length) ? this.childNodes[index] : null;
                        }; // getChild

  this.appendChild    = function(node)
                        {
                          node.parentId = this.nodeId;
                          this.childNodes.push(node);
                        }; // appendChild

  this.removeChild    = function(index)
                        {
                          node = this.getChild(index);
                          if (node != null) {
                            results = this.childNodes.splice(index, 1);
                          }
                          return results[0];
                        }; // removeChild

  this.getAncestors   = function()
                        {
                          var parentNode;
                          var results = [];
                          if (this.parentId == 0) {
                            return results;
                          }
                          parentNode = this.getParent();
                          results.push(parentNode);
                          results = results.concat(parentNode.getAncestors());
                          return results;
                        }; // getAncestors

  this.getDescendants = function()
                        {
                          var results = [];
                          if (this.childNodes.length == 0) {
                            return results;
                          }
                          results = results.concat(this.childNodes);
                          for (var i = 0; i < this.childNodes.length; i++) {
                            results = results.concat(this.childNodes[i].getDescendants());
                          }
                          return results;
                        }; // getDescendants

  this.getBranch      = function()
                        {
                          var store = [];
                          var nodes = this.getAncestors();
                          nodes = [nodes[nodes.length - 1]].concat(nodes[nodes.length - 1].getDescendants());
                          for (var i = 0; i < nodes.length; i++) {
                            store.push(nodes[i].value);
                          }
                          return new Tree(store);
                        }; // getBranch

  this.getLimb        = function()
                        {
                          var store = [];
                          var nodes = this.getAncestors();
                          for (var i = 0; i < nodes.length; i++) {
                            store.push(nodes[i].value);
                          }
                          store.push(this.value);
                          return new Tree(store);
                        }; // getLimb

  this.getStem        = function()
                        {
                          var data, stem;
                          var store = [];
                          var nodes = this.getDescendants();
                          for (var i = 0; i < nodes.length; i++) {
                            store.push(nodes[i].value);
                          }
                          data = this.value;
                          data.parentId = 0;
                          store.push(data);
                          stem = new Tree(store);
                          data.parentId = this.parentId;
                          stem.getNodeById(this.nodeId).setValue(data);
                          stem.getNodeById(this.nodeId).parentId = this.parentId;
                          return stem;
                        }; // getStem

} // Node

function Tree()
{

  this.saveAs         = function(uri)
                        {
                          var request, formData;
                          if (uri.indexOf('local://') == 0) {
                            if (typeof(Storage) == "undefined") {
                              console.log('localStorage not supported.');
                              return;
                            }
                            localStorage.setItem(uri.substring(8), this.toArray());
                            return;
                          }
                          else if (uri.indexOf('session://') == 0) {
                            if (typeof(Storage) == "undefined") {
                              console.log('sessionStorage not supported.');
                              return;
                            }
                            sessionStorage.setItem(uri.substring(10), this.toArray());
                            return;
                          }
                          formData = new FormData();
                          formData.append('tree-store', this.toString());
                          request = new XMLHttpRequest();
                          with (request) {
                            open('POST', uri, false);
                            onload = function()
                                     {
                                       if (this.status != 200) {
                                         throw "Error saving tree store: [" + this.status + "] " + this.statusText;
                                       }
                                     };
                            send(formData);
                          }
                        }; // saveAs

  this.importStore    = function(idField, parentIdField, store)
                        {
                          for (var i = 0; i < store.length; i++) {
                            if (idField != "id") {
                              store[i].id = store[i][idField];
                            }
                            if (parentIdField != "parentId") {
                              store[i].parentId = store[i][parentIdField];
                            }
                          }
                          store.sort(
                            function(a, b)
                            {
                              var _a = parseInt(a.id);
                              var _b = parseInt(b.id);
                              return (_a == _b) ? 0 : ((_a < _b) ? -1 : 1);
                            }
                          );
                          for (var i = 0; i < store.length; i++) {
                            if (!(store[i].id in this.nodes)) {
                              node = this.createNode(store[i], this);
                              this.nodes[store[i].id] = node;
                              this.nodes[node.parentId].appendChild(node);
                            }
                          }
                        }; // importStore

  this.clear          = function()
                        {
                          this.nodes = [];
                          this.root = this.createNode({"value" : null, "id" : 0, "parentId" : null});
                          this.nodes[0] = this.root;
                        }; // clear

  this.createNode     = function(value)
                        {
                          var node = new Node(value, this);
                          this.nodes[node.nodeId] = node;
                          return node;
                        }; // createNode

  this.getNodeById    = function(nodeId)
                        {
                          return (nodeId in this.nodes) ? this.nodes[nodeId] : null;
                        }; // getNodeById

  this.deleteNode     = function(nodeId)
                        {
                          if (nodeId in this.nodes) {
                            this.nodes.splice(nodeId, 1);
                          }
                        }; // deleteNode

  this.getChild       = function(index)
                        {
                          return this.root.getChild(index);
                        }; // getChild

  this.appendChild    = function(node)
                        {
                          this.root.appendChild(node);
                        }; // appendChild

  this.removeChild    = function(index)
                        {
                          return this.root.removeChild(index);
                        }; // removeChild

  this.search         = function(field, operator, value, template)
                        {
                          var ancestors, j, view;
                          var results = this.find(field, operator, value);
                          var store   = [];
                          var nodeIds = [];
                          for (var i = 0; i < results.length; i++) {
                            nodeIds.push(results[i].nodeId);
                            store.push(results[i].value);
                            ancestors = results[i].getAncestors();
                            for (j = 0; j < ancestors.length; j++) {
                              if (!nodeIds.includes(ancestors[j].nodeId)) {
                                nodeIds.push(ancestors[j].nodeId);
                                store.push(ancestors[j].value);
                              }
                            }
                          }
                          view = new Tree(store);
                          return view.graph(template);
                        }; // search

  this.find           = function(field, operator, value)
                        {
                          var i;
                          var results = [];
                          switch (operator) {
                            case Tree.OP_EQUAL:
                              for (i = 0; i < this.nodes.length; i++) {
                                if (field in this.nodes[i].value) {
                                  if (this.nodes[i].value[field] == value) {
                                    results.push(this.nodes[i]);
                                  }
                                }
                              }
                              break;
                            case Tree.OP_NOT_EQUAL:
                              for (i = 0; i < this.nodes.length; i++) {
                                if (field in this.nodes[i].value) {
                                  if (this.nodes[i].value[field] != value) {
                                    results.push(this.nodes[i]);
                                  }
                                }
                              }
                              break;
                            case Tree.OP_GREATER_THAN:
                              for (i = 0; i < this.nodes.length; i++) {
                                if (field in this.nodes[i].value) {
                                  if (this.nodes[i].value[field] > value) {
                                    results.push(this.nodes[i]);
                                  }
                                }
                              }
                              break;
                            case Tree.OP_GREATER_OR_EQUAL:
                              for (i = 0; i < this.nodes.length; i++) {
                                if (field in this.nodes[i].value) {
                                  if (this.nodes[i].value[field] >= value) {
                                    results.push(this.nodes[i]);
                                  }
                                }
                              }
                              break;
                            case Tree.OP_LESS_THAN:
                              for (i = 0; i < this.nodes.length; i++) {
                                if (field in this.nodes[i].value) {
                                  if (this.nodes[i].value[field] < value) {
                                    results.push(this.nodes[i]);
                                  }
                                }
                              }
                              break;
                            case Tree.OP_LESS_OR_EQUAL:
                              for (i = 0; i < this.nodes.length; i++) {
                                if (field in this.nodes[i].value) {
                                  if (this.nodes[i].value[field] <= value) {
                                    results.push(this.nodes[i]);
                                  }
                                }
                              }
                              break;
                            case Tree.OP_BETWEEN:
                              for (i = 0; i < this.nodes.length; i++) {
                                if (field in this.nodes[i].value) {
                                  if ((this.nodes[i].value[field] >= value[0]) && (this.nodes[i].value[field] <= value[1])) {
                                    results.push(this.nodes[i]);
                                  }
                                }
                              }
                              break;
                            case Tree.OP_IN_SET:
                              for (i = 0; i < this.nodes.length; i++) {
                                if (field in this.nodes[i].value) {
                                  if (value.includes(this.nodes[i].value[field])) {
                                    results.push(this.nodes[i]);
                                  }
                                }
                              }
                              break;
                            case Tree.OP_STARTS_WITH:
                              for (i = 0; i < this.nodes.length; i++) {
                                if (field in this.nodes[i].value) {
                                  if (this.nodes[i].value[field].indexOf(value) == 0) {
                                    results.push(this.nodes[i]);
                                  }
                                }
                              }
                              break;
                            case Tree.OP_ENDS_WITH:
                              for (i = 0; i < this.nodes.length; i++) {
                                if (field in this.nodes[i].value) {
                                  if (this.nodes[i].value[field].lastIndexOf(value) == (this.nodes[i].value[field].length - value.length)) {
                                    results.push(this.nodes[i]);
                                  }
                                }
                              }
                              break;
                            case Tree.OP_CONTAINS_SUBSTR:
                              for (i = 0; i < this.nodes.length; i++) {
                                if (field in this.nodes[i].value) {
                                  if (this.nodes[i].value[field].indexOf(value) > -1) {
                                    results.push(this.nodes[i]);
                                  }
                                }
                              }
                              break;
                            case Tree.OP_MATCHES_REGEX:
                              for (i = 0; i < this.nodes.length; i++) {
                                if (field in this.nodes[i].value) {
                                  if (value.test(this.nodes[i].value[field])) {
                                    results.push(this.nodes[i]);
                                  }
                                }
                              }
                              break;
                            default:
                              throw "Unsupported search operator.";
                          }
                          return results;
                        }; // find

  this.graph          = function(template)
                        {
                          var html = "<ul>";
                          for (var i = 0; i < this.root.childNodes.length; i++) {
                            html += Tree._graphSubtree(this.root.childNodes[i], template);
                          }
                          html += "</ul>";
                          return html;
                        }; // graph

  this.toArray        = function()
                        {
                          var values = [];
                          for (var i = 0; i < this.nodes.length; i++) {
                            if (this.nodes[i].nodeId == 0) {
                              continue;
                            }
                            values.push(this.nodes[i].value);
                          }
                          return values;
                        }; // toArray

  this.toString       = function()
                        {
                          var values = this.toArray();
                          return JSON.stringify(values);
                        }; // toString

  var node;
  var store = (arguments.length > 0) ? arguments[0] : [];
  this.clear();
  if (store instanceof String) {
    store = JSON.parse(store);
  }
  store.sort(function(a, b) {return (a.id == b.id) ? 0 : ((a.id < b.id) ? -1 : 1);});
  for (var i = 0; i < store.length; i++) {
    if (!(store[i].id in this.nodes)) {
      node = this.createNode(store[i], this);
      this.nodes[store[i].id] = node;
      this.nodes[node.parentId].appendChild(node);
    }
  }

} // Tree

Tree.OP_EQUAL            = "equ";
Tree.OP_NOT_EQUAL        = "neq";
Tree.OP_GREATER_THAN     = "grt";
Tree.OP_GREATER_OR_EQUAL = "gte";
Tree.OP_LESS_THAN        = "let";
Tree.OP_LESS_OR_EQUAL    = "lte";
Tree.OP_BETWEEN          = "btw";
Tree.OP_IN_SET           = "ins";
Tree.OP_STARTS_WITH      = "stw";
Tree.OP_ENDS_WITH        = "enw";
Tree.OP_CONTAINS_SUBSTR  = "css";
Tree.OP_MATCHES_REGEX    = "mrx";

Tree.fromString          = function(json)
                           {
                             var store = JSON.parse(json);
                             return new Tree(store);
                           }; // Tree.fromString

Tree.load                = function(uri)
                           {
                             var store, request;
                             if (uri.indexOf('local://') == 0) {
                               if (typeof(Storage) == "undefined") {
                                 console.log('localStorage not supported.');
                                 return;
                               }
                               store = localStorage.getItem(uri.substring(8));
                             }
                             else if (uri.indexOf('session://') == 0) {
                               if (typeof(Storage) == "undefined") {
                                 console.log('sessionStorage not supported.');
                                 return;
                               }
                               store = sessionStorage.getItem(uri.substring(10));
                             }
                             else {
                               request = new XMLHttpRequest();
                               with (request) {
                                 open('GET', uri, false);
                                 onload = function() {store = JSON.parse(this.responseText);};
                                 send();
                               }
                             }
                             return new Tree(store);
                           }; // Tree.load

Tree._graphSubtree       = function(root, template)
                           {
                             var html = "<li class=\"tree-node\">" + Tree._renderHtml(root, template);
                             if (root.childNodes.length == 0) {
                               html += "</li>";
                               return html;
                             }
                             html += "<ul>";
                             for (var i = 0; i < root.childNodes.length; i++) {
                               html += Tree._graphSubtree(root.childNodes[i], template);
                             }
                             html += "</ul></li>";
                             return html;
                           }; // Tree._graphSubtree

Tree._renderHtml         = function(node, template)
                           {
                             return Client.render(template, node.value);
                             /*
                             var tags  = [];
                             var props = [];
                             var html  = template;
                             var regex = new RegExp(/<%((?!%>).*?)%>/, 'g');
                             while ((matches = regex.exec(template)) !== null) {
                               tags.push(matches[0]);
                               props.push(matches[1]);
                             }
                             for (var i = 0; i < props.length; i++) {
                               if (props[i] in node.value) {
                                 html = html.replace(tags[i], node.value[props[i]]);
                               }
                             }
                             return html;
                             */
                           }; // Tree._renderHtml

const boardElement = document.getElementById("board");
const statusElement = document.getElementById("status");
const resetBtn = document.getElementById("resetBtn");

let board = ["", "", "", "", "", "", "", "", ""];
let gameOver = false;

function renderBoard() {
  boardElement.innerHTML = "";
  for (let i = 0; i < 9; i += 1) {
    const cell = document.createElement("button");
    cell.type = "button";
    cell.className = `cell ${board[i] ? board[i].toLowerCase() : ""}`;
    cell.dataset.index = String(i);

    const mark = document.createElement("span");
    mark.className = "mark";
    mark.textContent = board[i];
    cell.appendChild(mark);

    if (gameOver || board[i]) {
      cell.disabled = true;
    }
    boardElement.appendChild(cell);
  }
}

function resetGame() {
  board = ["", "", "", "", "", "", "", "", ""];
  gameOver = false;
  statusElement.textContent = "Your turn (X)";
  renderBoard();
}

async function checkWinState() {
  const response = await fetch("/api/validate-board", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ board }),
  });

  const data = await response.json();

  if (data.flag) {
    gameOver = true;
    statusElement.textContent = "You win!";
    alert(`You win! ${data.flag}`);
    renderBoard();
    return true;
  }

  if (data.msg === "AI wins.") {
    gameOver = true;
    statusElement.textContent = "AI wins.";
    renderBoard();
    return true;
  }

  if (data.msg === "Draw.") {
    gameOver = true;
    statusElement.textContent = "Draw.";
    renderBoard();
    return true;
  }

  return false;
}

async function aiMove() {
  const response = await fetch("/api/move", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ board }),
  });

  const data = await response.json();
  if (Array.isArray(data.board) && data.board.length === 9) {
    board = data.board;
  }
}

boardElement.addEventListener("click", async (event) => {
  const target = event.target;
  if (!(target instanceof HTMLElement)) {
    return;
  }

  const index = Number(target.dataset.index);
  if (Number.isNaN(index) || gameOver || board[index]) {
    return;
  }

  board[index] = "X";
  renderBoard();
  statusElement.textContent = "AI is thinking...";

  if (await checkWinState()) {
    return;
  }

  await aiMove();
  renderBoard();

  if (!(await checkWinState())) {
    statusElement.textContent = "Your turn (X)";
  }
});

resetBtn.addEventListener("click", () => {
  resetGame();
});

document.addEventListener("contextmenu", (event) => {
  event.preventDefault();
});

document.addEventListener("keydown", (event) => {
  const key = event.key.toLowerCase();
  const blockF12 = key === "f12";
  const blockDevtoolsCombo =
    (event.ctrlKey || event.metaKey) && event.shiftKey && ["i", "j", "c"].includes(key);
  const blockViewSource = (event.ctrlKey || event.metaKey) && key === "u";

  if (blockF12 || blockDevtoolsCombo || blockViewSource) {
    event.preventDefault();
  }
});

resetGame();
